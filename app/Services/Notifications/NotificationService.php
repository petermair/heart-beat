<?php

namespace App\Services\Notifications;

use App\Models\DeviceMonitoringResult;
use App\Models\NotificationSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class NotificationService
{
    public function sendNotification(NotificationSetting $setting, DeviceMonitoringResult $result): void
    {
        if (!$this->shouldSendNotification($setting, $result)) {
            return;
        }

        try {
            match($setting->channel) {
                'email' => $this->sendEmailNotification($setting, $result),
                'slack' => $this->sendSlackNotification($setting, $result),
                'webhook' => $this->sendWebhookNotification($setting, $result),
                default => throw new \InvalidArgumentException("Unknown notification channel: {$setting->channel}"),
            };

            // Update last notification timestamp
            $this->updateNotificationTimestamp($setting, $result);
        } catch (\Exception $e) {
            Log::error("Failed to send notification", [
                'channel' => $setting->channel,
                'error' => $e->getMessage(),
                'setting_id' => $setting->id,
                'result_id' => $result->id,
            ]);
        }
    }

    private function shouldSendNotification(NotificationSetting $setting, DeviceMonitoringResult $result): bool
    {
        if (!$setting->is_active) {
            return false;
        }

        $conditions = $setting->conditions ?? $setting->getDefaultConditions();
        $cacheKey = "notification:{$setting->id}:last_sent";
        $lastSent = Cache::get($cacheKey);

        // Check throttling
        if ($lastSent && now()->diffInMinutes($lastSent) < ($conditions['throttle_minutes'] ?? 15)) {
            return false;
        }

        // Check failure conditions
        if (!$result->success && $conditions['on_failure']) {
            $recentFailures = DeviceMonitoringResult::query()
                ->where('device_id', $result->device_id)
                ->where('created_at', '>=', now()->subSeconds($conditions['failure_window'] ?? 3600))
                ->where('success', false)
                ->count();

            return $recentFailures >= ($conditions['min_failures'] ?? 1);
        }

        // Check recovery conditions
        if ($result->success && $conditions['on_recovery']) {
            $previousResult = DeviceMonitoringResult::query()
                ->where('device_id', $result->device_id)
                ->where('id', '<', $result->id)
                ->orderByDesc('id')
                ->first();

            return $previousResult && !$previousResult->success;
        }

        return false;
    }

    private function sendEmailNotification(NotificationSetting $setting, DeviceMonitoringResult $result): void
    {
        $config = $setting->configuration;
        $device = $result->device;
        $scenario = $result->testScenario;

        $subject = $result->success
            ? "Device {$device->name} has recovered"
            : "Device {$device->name} test failed";

        Mail::raw(
            $this->formatNotificationMessage($result),
            function ($message) use ($config, $subject) {
                $message->subject($subject);
                
                foreach ($config['recipients'] ?? [] as $recipient) {
                    $message->to($recipient);
                }
                
                foreach ($config['cc'] ?? [] as $cc) {
                    $message->cc($cc);
                }
                
                foreach ($config['bcc'] ?? [] as $bcc) {
                    $message->bcc($bcc);
                }
            }
        );
    }

    private function sendSlackNotification(NotificationSetting $setting, DeviceMonitoringResult $result): void
    {
        $config = $setting->configuration;
        
        Http::post($config['webhook_url'], [
            'channel' => $config['channel'] ?? null,
            'username' => $config['username'] ?? 'Heart-Beat Monitor',
            'text' => $this->formatNotificationMessage($result),
            'icon_emoji' => $result->success ? ':white_check_mark:' : ':x:',
        ]);
    }

    private function sendWebhookNotification(NotificationSetting $setting, DeviceMonitoringResult $result): void
    {
        $config = $setting->configuration;
        
        Http::withHeaders($config['headers'] ?? [])
            ->{strtolower($config['method'] ?? 'post')}($config['url'], [
                'device' => $result->device->toArray(),
                'result' => $result->toArray(),
                'message' => $this->formatNotificationMessage($result),
            ]);
    }

    private function formatNotificationMessage(DeviceMonitoringResult $result): string
    {
        $device = $result->device;
        $scenario = $result->testScenario;
        $status = $result->success ? 'PASSED' : 'FAILED';

        $message = "Test Status: {$status}\n";
        $message .= "Device: {$device->name}\n";
        $message .= "Test: {$scenario->name}\n";
        $message .= "Type: {$scenario->test_type}\n";
        $message .= "Response Time: {$result->response_time_ms}ms\n";

        if (!$result->success) {
            $message .= "Error: {$result->error_message}\n";
        }

        if ($result->metadata) {
            $message .= "\nAdditional Information:\n";
            foreach ($result->metadata as $key => $value) {
                $message .= "- {$key}: " . json_encode($value) . "\n";
            }
        }

        return $message;
    }

    private function updateNotificationTimestamp(NotificationSetting $setting, DeviceMonitoringResult $result): void
    {
        $cacheKey = "notification:{$setting->id}:last_sent";
        Cache::put($cacheKey, now(), now()->addDay());
    }
}
