<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use App\Models\GenerationJob;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handle(Request $request, $model_slug)
    {
        $model = AiModel::where('slug', $model_slug)->first();

        if (!$model) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        // Verify HMAC signature
        $signatureHeader = $request->header('X-Webhook-Signature'); // Example header
        $payload = $request->getContent();
        
        if ($model->webhook_secret) {
            $expectedSignature = hash_hmac('sha256', $payload, $model->webhook_secret);
            if (!hash_equals($expectedSignature, $signatureHeader ?? '')) {
                return response()->json(['error' => 'WEBHOOK_INVALID_SIGNATURE'], 401);
            }
        }

        $data = $request->json()->all();
        $externalJobId = $data['external_job_id'] ?? null;
        $resultImageUrl = $data['result_image_url'] ?? null;
        $error = $data['error'] ?? null;

        if (!$externalJobId) {
            return response()->json(['error' => 'Missing external_job_id'], 400);
        }

        $job = GenerationJob::where('external_job_id', $externalJobId)->first();

        if (!$job) {
            return response()->json(['error' => 'JOB_NOT_FOUND'], 404);
        }

        if ($error) {
            $job->status = 'failed';
            $job->error_message = $error;
            
            // Refund credits
            $creditBalance = $job->user->creditBalances()->where('model_id', $job->model_id)->first();
            if ($creditBalance) {
                $creditBalance->credits_remaining += $job->credits_consumed;
                $creditBalance->save();
            }
        } else {
            $job->status = 'completed';
            $job->result_image_url = $resultImageUrl;
        }

        $job->webhook_received_at = now();
        $job->save();

        // Send push notification here
        
        return response()->json(['status' => 'success'], 200);
    }
}
