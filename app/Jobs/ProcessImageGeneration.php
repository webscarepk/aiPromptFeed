<?php

namespace App\Jobs;

use App\Models\GenerationJob;
use App\Services\ModelWorkers\ModelWorkerRegistry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessImageGeneration implements ShouldQueue
{
    use Queueable;

    public $job;

    /**
     * Create a new job instance.
     */
    public function __construct(GenerationJob $job)
    {
        $this->job = $job;
    }

    /**
     * Execute the job.
     */
    public function handle(ModelWorkerRegistry $registry): void
    {
        try {
            $this->job->status = 'processing';
            $this->job->save();

            $model = $this->job->aiModel;
            $worker = $registry->getWorker('custom');

            $externalId = $worker->submitJob($this->job);

            $this->job->external_job_id = $externalId;
            $this->job->save();

        } catch (\Exception $e) {
            Log::error('Generation Job Failed: ' . $e->getMessage());
            
            $this->job->status = 'failed';
            $this->job->error_message = $e->getMessage();
            $this->job->save();

            // Refund credits
            $creditBalance = $this->job->user->creditBalances()
                ->where('model_id', $this->job->model_id)
                ->first();
                
            if ($creditBalance) {
                $creditBalance->credits_remaining += $this->job->credits_consumed;
                $creditBalance->save();
            }
        }
    }
}
