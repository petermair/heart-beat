<?php

namespace App\Services\Testing;

class RoutingTest implements TestInterface
{
    public function __construct(
        protected string $destination,
        protected array $options = []
    ) {}

    public function execute(): TestResult
    {
        // TODO: Implement routing test
        return new TestResult(true);
    }

    public function getName(): string
    {
        return 'Routing Test';
    }
}
