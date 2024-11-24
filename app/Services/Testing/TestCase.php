<?php

namespace App\Services\Testing;

abstract class TestCase
{
    protected string $id;

    protected string $status = 'pending';

    protected int $retries = 0;

    protected ?string $lastError = null;

    protected array $results = [];

    public function __construct(
        protected array $config
    ) {
        $this->id = uniqid('test_', true);
    }

    /**
     * Execute the test
     */
    abstract public function execute(): void;

    /**
     * Get test unique identifier
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get current retry count
     */
    public function getRetries(): int
    {
        return $this->retries;
    }

    /**
     * Get maximum allowed retries
     */
    public function getMaxRetries(): int
    {
        return $this->config['retries'] ?? 3;
    }

    /**
     * Get test timeout in seconds
     */
    public function getTimeout(): int
    {
        return $this->config['timeout'] ?? 30;
    }

    /**
     * Increment retry counter
     */
    public function incrementRetries(): void
    {
        $this->retries++;
    }

    /**
     * Get test status
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Set test status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * Get last error message
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Set error message
     */
    public function setError(string $error): void
    {
        $this->lastError = $error;
    }

    /**
     * Get test results
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Add test result
     */
    protected function addResult(string $key, mixed $value): void
    {
        $this->results[$key] = $value;
    }
}
