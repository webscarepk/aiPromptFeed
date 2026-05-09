<?php

namespace App\Services\ModelWorkers;

use App\Models\GenerationJob;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class MockWorker implements ModelWorker
{
    public function submitJob(GenerationJob $job): string
    {
        // Mock sending job to external API
        $externalJobId = (string) Str::uuid();

        // Simulate async processing and webhook hit (for local development only)
        // Normally, the 3rd party API hits the webhook endpoint itself later
        $webhookUrl = url('/api/v1/webhooks/' . $job->aiModel->slug);
        
        $payload = [
            'external_job_id' => $externalJobId,
            'result_image_url' => 'https://via.placeholder.com/512x512?text=Generated+Image',
            'error' => null
        ];

        $signature = hash_hmac('sha256', json_encode($payload), $job->aiModel->webhook_secret ?? '');

        // Hit the webhook endpoint asynchronously in 2 seconds
        // In real world, this is done by the third party
        // We'll skip local http call to avoid blocking. Real webhook receiver works.
        
        return $externalJobId;
    }

    public function parseWebhook(array $payload): array
    {
        return [
            'external_job_id' => $payload['external_job_id'] ?? null,
            'result_image_url' => $payload['result_image_url'] ?? null,
            'error' => $payload['error'] ?? null,
        ];
    }

    public function verifySignature(string $rawBody, string $signatureHeader, string $secret): bool
    {
        if (empty($secret)) {
            return true;
        }

        $expectedSignature = hash_hmac('sha256', $rawBody, $secret);
        return hash_equals($expectedSignature, $signatureHeader);
    }
}
