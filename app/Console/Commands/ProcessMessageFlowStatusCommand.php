<?php

namespace App\Console\Commands;

use App\Models\TestResult;
use App\Services\MessageFlow\MessageFlowStatusService;
use Illuminate\Console\Command;

class ProcessMessageFlowStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message-flow:process-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process message flows status, check for timeouts, and update service statuses';

    /**
     * Execute the console command.
     */
    public function handle(MessageFlowStatusService $messageFlowStatusService): void
    {
        $this->info('Processing message flow statuses...');
        
        // Process message flows and update service statuses
        $testResults = TestResult::where('status', 'PENDING')->get();        
        foreach($testResults as $testResult) {
          $messageFlowStatusService->processTestResult($testResult);   
        }

         
        
        $this->info('Message flow status processing completed.');
    }
}
