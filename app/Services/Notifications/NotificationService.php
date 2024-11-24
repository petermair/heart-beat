<?php

namespace App\Services\Notifications;

use App\Models\DeviceMonitoringResult;
use App\Models\NotificationType;
use App\Models\TestScenarioNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class NotificationService
{
    public function sendNotification(TestScenarioNotification $notification, DeviceMonitoringResult $result): void
    {
        if (!$this->shouldSendNotification($notification, $result)) {
            return;
        }

        $notificationType = $notification->notificationType;
        $testScenario = $notification->testScenario;

        switch ($notificationType->type) {
            case 'email':
                $this->sendEmailNotification($notificationType, $result);
                break;
            case 'slack':
                $this->sendSlackNotification($notificationType, $result);
                break;
            case 'webhook':
                $this->sendWebhookNotification($notificationType, $result);
                break;
        }

        $this->updateNotificationTimestamp($notification, $result);
    }

    private function shouldSendNotification(TestScenarioNotification $notification, DeviceMonitoringResult $result): bool
    {
        if (!$notification->notificationType->is_active) {
            return false;
        }

        // Check if enough time has passed since the last notification
        if ($notification->last_notification_at) {
            $minInterval = $notification->notificationType->configuration['min_interval'] ?? 300; // Default 5 minutes
            $nextAllowed = Carbon::parse($notification->last_notification_at)->addSeconds($minInterval);
            if (Carbon::now()->lt($nextAllowed)) {
                return false;
            }
        }

        return true;
    }

    private function sendEmailNotification(NotificationType $type, DeviceMonitoringResult $result): void
    {
        $config = $type->configuration;
        $recipients = $config['recipients'] ?? [];
        
        if (empty($recipients)) {
            return;
        }

        $testScenario = $result->testScenario;
        $subject = "Test Scenario Alert: {$testScenario->name}";
        $body = "Test scenario {$testScenario->name} has failed.\n\n";
        $body .= "Details:\n";
        $body .= "- Success Rate (1h): {$testScenario->success_rate_1h}%\n";
        $body .= "- Success Rate (24h): {$testScenario->success_rate_24h}%\n";
        $body .= "- Last Success: " . ($testScenario->last_success_at ? $testScenario->last_success_at->diffForHumans() : 'Never') . "\n";
        $body .= "- Error: {$result->error_message}\n";

        Mail::raw($body, function ($message) use ($recipients, $subject) {
            $message->to($recipients)
                   ->subject($subject);
        });
    }

    private function sendSlackNotification(NotificationType $type, DeviceMonitoringResult $result): void
    {
        $config = $type->configuration;
        $webhookUrl = $config['webhook_url'] ?? null;
        
        if (!$webhookUrl) {
            return;
        }

        $testScenario = $result->testScenario;
        $message = [
            'text' => "Test Scenario Alert: {$testScenario->name}\n" .
                     "Success Rate (1h): {$testScenario->success_rate_1h}%\n" .
                     "Success Rate (24h): {$testScenario->success_rate_24h}%\n" .
                     "Error: {$result->error_message}"
        ];

        Http::post($webhookUrl, $message);
    }

    private function sendWebhookNotification(NotificationType $type, DeviceMonitoringResult $result): void
    {
        $config = $type->configuration;
        $webhookUrl = $config['url'] ?? null;
        $method = strtoupper($config['method'] ?? 'POST');
        $headers = $config['headers'] ?? [];
        
        if (!$webhookUrl) {
            return;
        }

        $testScenario = $result->testScenario;
        $payload = [
            'test_scenario' => [
                'id' => $testScenario->id,
                'name' => $testScenario->name,
                'success_rate_1h' => $testScenario->success_rate_1h,
                'success_rate_24h' => $testScenario->success_rate_24h,
                'last_success_at' => $testScenario->last_success_at,
            ],
            'result' => [
                'id' => $result->id,
                'error_message' => $result->error_message,
                'created_at' => $result->created_at,
            ]
        ];

        Http::withHeaders($headers)->send($method, $webhookUrl, ['json' => $payload]);
    }

    private function updateNotificationTimestamp(TestScenarioNotification $notification, DeviceMonitoringResult $result): void
    {
        $notification->update([
            'last_notification_at' => now(),
            'last_result_id' => $result->id
        ]);
    }
}
