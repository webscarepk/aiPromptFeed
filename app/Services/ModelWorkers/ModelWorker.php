<?php

namespace App\Services\ModelWorkers;

use App\Models\GenerationJob;

interface ModelWorker
{
    /**
     * Submit a job to the third-party provider.
     * 
     * @param GenerationJob $job
     * @return string The external job ID.
     */
    public function submitJob(GenerationJob $job): string;

    /**
     * Parse incoming webhook payload to extract relevant fields.
     * 
     * @param array $payload
     * @return array [ 'external_job_id' => string, 'result_image_url' => ?string, 'error' => ?string ]
     */
    public function parseWebhook(array $payload): array;

    /**
     * Verify the webhook signature.
     * 
     * @param string $rawBody
     * @param string $signatureHeader
     * @param string $secret
     * @return bool
     */
    public function verifySignature(string $rawBody, string $signatureHeader, string $secret): bool;
}
