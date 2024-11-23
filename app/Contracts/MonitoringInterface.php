<?php

namespace App\Contracts;

interface MonitoringInterface
{
    /**
     * Check the health status of the service.
     *
     * @return array<string, mixed>
     */
    public function checkHealth(): array;

    /**
     * Get response time of the service.
     *
     * @return float Response time in milliseconds
     */
    public function getResponseTime(): float;

    /**
     * Check if the service is available.
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Get SSL certificate information if available.
     *
     * @return array<string, mixed>|null
     */
    public function getSslCertificateInfo(): ?array;

    /**
     * Get error rate over the specified time window.
     *
     * @param int $window Time window in seconds
     * @return float Error rate as a percentage
     */
    public function getErrorRate(int $window): float;

    /**
     * Get service-specific metrics.
     *
     * @return array<string, mixed>
     */
    public function getMetrics(): array;
}
