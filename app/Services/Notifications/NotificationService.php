<?php

namespace App\Services\Notifications;

use App\Models\TestResult;
use App\Models\NotificationType;
use App\Models\TestScenarioNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function sendNotification(TestScenarioNotification $notification, TestResult $result, array $context = []): void
    {
        if (! $this->shouldSendNotification($notification, $result)) {
            return;
        }

        $notificationType = $notification->notificationType;
        $testScenario = $notification->testScenario;

        switch ($notificationType->type) {
            case 'email':
                $this->sendEmailNotification($notificationType, $result, $context);
                break;
            case 'slack':
                $this->sendSlackNotification($notificationType, $result, $context);
                break;
            case 'webhook':
                $this->sendWebhookNotification($notificationType, $result, $context);
                break;
        }

        $this->updateNotificationTimestamp($notification, $result);
    }

    private function shouldSendNotification(TestScenarioNotification $notification, TestResult $result): bool
    {
        if (! $notification->notificationType->is_active) {
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

    private function sendEmailNotification(NotificationType $type, TestResult $result, array $context = []): void
    {
        $config = $type->configuration;
        $recipients = $config['recipients'] ?? [];

        if (empty($recipients)) {
            return;
        }

        $body = $this->buildEmailBody($result, $context);
        $subject = "Test Scenario Alert: {$result->testScenario->name}";

        Mail::raw($body, function ($message) use ($recipients, $subject) {
            $message->to($recipients)
                ->subject($subject);
        });
    }

    private function sendSlackNotification(NotificationType $type, TestResult $result, array $context = []): void
    {
        $config = $type->configuration;
        $webhookUrl = $config['webhook_url'] ?? null;

        if (! $webhookUrl) {
            return;
        }

        $message = $this->buildSlackMessage($result, $context);

        Http::post($webhookUrl, $message);
    }

    private function sendWebhookNotification(NotificationType $type, TestResult $result, array $context = []): void
    {
        $config = $type->configuration;
        $webhookUrl = $config['url'] ?? null;
        $method = strtoupper($config['method'] ?? 'POST');
        $headers = $config['headers'] ?? [];

        if (! $webhookUrl) {
            return;
        }

        $payload = $this->buildWebhookPayload($result, $context);

        Http::withHeaders($headers)->send($method, $webhookUrl, ['json' => $payload]);
    }

    private function updateNotificationTimestamp(TestScenarioNotification $notification, TestResult $result): void
    {
        $notification->update([
            'last_notification_at' => now(),
            'last_result_id' => $result->id,
        ]);
    }

    private function buildEmailBody(TestResult $result, array $context = []): string
    {
        $body = "Test Result Status: {$result->status}\n";
        $body .= "Test Scenario: {$result->testScenario->name}\n";
        
        if (!empty($context)) {
            $body .= "\nService Status:\n";
            $body .= "Service: {$context['service']}\n";
            $body .= "Success Rate: {$context['success_rate']}%\n";
            $body .= "Total Flows: {$context['total_flows']}\n";
            $body .= "Last Success: {$context['last_success']}\n";
            $body .= "Downtime Duration: {$context['downtime_duration']} minutes\n";
        }
        
        if ($result->error_message) {
            $body .= "\nError Message: {$result->error_message}\n";
        }
        
        return $body;
    }

    private function buildSlackMessage(TestResult $result, array $context = []): array
    {
        $blocks = [
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*Test Result Status: {$result->status}*\nTest Scenario: {$result->testScenario->name}"
                ]
            ]
        ];

        if (!empty($context)) {
            $blocks[] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*Service Status:*\n" .
                             "Service: {$context['service']}\n" .
                             "Success Rate: {$context['success_rate']}%\n" .
                             "Total Flows: {$context['total_flows']}\n" .
                             "Last Success: {$context['last_success']}\n" .
                             "Downtime Duration: {$context['downtime_duration']} minutes"
                ]
            ];
        }

        if ($result->error_message) {
            $blocks[] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*Error Message:*\n{$result->error_message}"
                ]
            ];
        }

        return [
            'blocks' => $blocks
        ];
    }

    private function buildWebhookPayload(TestResult $result, array $context = []): array
    {
        $payload = [
            'status' => $result->status,
            'test_scenario' => $result->testScenario->name,
            'timestamp' => now()->toIso8601String()
        ];

        if (!empty($context)) {
            $payload['service_status'] = [
                'service' => $context['service'],
                'success_rate' => $context['success_rate'],
                'total_flows' => $context['total_flows'],
                'last_success' => $context['last_success'],
                'downtime_duration' => $context['downtime_duration']
            ];
        }

        if ($result->error_message) {
            $payload['error_message'] = $result->error_message;
        }

        return $payload;
    }
}
