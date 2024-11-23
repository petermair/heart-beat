<?php

namespace App\Services\Testing;

interface TestInterface
{
    public function execute(): TestResult;
    public function getName(): string;
}
