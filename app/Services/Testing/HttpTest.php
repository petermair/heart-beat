<?php

namespace App\Services\Testing;

class HttpTest implements TestInterface
{
    public function __construct(
        protected string $url,
        protected array $options = []
    ) {}

    public function execute(): TestResult
    {
        // TODO: Implement HTTP test
        return new TestResult(true);
    }

    public function getName(): string
    {
        return 'HTTP Test';
    }
}
