<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use App\Models\CreditHistory;
use App\Models\GenerationJob;
use App\Models\UserCreditBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerationApiController extends Controller
{
    public function index(Request $request)
    {
        $jobs = $request->user()->generationJobs()->latest()->paginate(15);
        return response()->json($jobs);
    }

    public function show(Request $request, GenerationJob $job)
    {
        if ($job->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json($job);
    }

    public function store(Request $request)
    {
        $request->validate([
            'model_id' => 'required|exists:ai_models,id',
            'prompt'   => 'required|string|max:5000',
            // Allow single image (backward compat) OR array of up to 5 images
            'image'    => 'nullable|file|mimes:jpeg,png,webp|max:10240',
            'images'   => 'nullable|array|max:5',
            'images.*' => 'file|mimes:jpeg,png,webp|max:10240',
        ]);

        $user  = $request->user();
        $model = AiModel::find($request->model_id);

        if (!$model || !$model->is_active) {
            return response()->json(['error' => 'INVALID_MODEL'], 400);
        }

        try {
            DB::beginTransaction();

            $creditBalance = UserCreditBalance::where('user_id', $user->id)
                ->where('model_id', $model->id)
                ->lockForUpdate()
                ->first();

            if (!$creditBalance || $creditBalance->credits_remaining < $model->cost_per_use) {
                DB::rollBack();
                return response()->json([
                    'error'             => 'INSUFFICIENT_CREDITS',
                    'model_name'        => $model->name,
                    'credits_remaining' => $creditBalance ? $creditBalance->credits_remaining : 0,
                    'credits_required'  => $model->cost_per_use,
                    'upgrade_url'       => url('/plans')
                ], 402);
            }

            // Snapshot balance before deduction
            $balanceBefore = $creditBalance->credits_remaining;

            // Deduct credits
            $creditBalance->credits_remaining -= $model->cost_per_use;
            $creditBalance->save();

            $balanceAfter = $creditBalance->credits_remaining;

            // ------------------------------------------------------------------
            // Handle image uploads
            // ------------------------------------------------------------------
            $sourceImageUrl   = null;
            $sourceImagesUrls = [];

            // Single legacy image field
            if ($request->hasFile('image')) {
                $path           = $request->file('image')->store('generations/source', 'public');
                $sourceImageUrl = asset('storage/' . $path);
                $sourceImagesUrls[] = $sourceImageUrl;
            }

            // Multi-image array field (up to 5)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $img) {
                    $path               = $img->store('generations/source', 'public');
                    $url                = asset('storage/' . $path);
                    $sourceImagesUrls[] = $url;
                    if (!$sourceImageUrl) {
                        $sourceImageUrl = $url; // keep single field for backward compat
                    }
                }
            }

            // Create generation job
            $job = GenerationJob::create([
                'user_id'           => $user->id,
                'model_id'          => $model->id,
                'prompt'            => $request->prompt,
                'source_image_url'  => $sourceImageUrl,
                'source_images_urls'=> !empty($sourceImagesUrls) ? $sourceImagesUrls : null,
                'status'            => 'pending',
                'credits_consumed'  => $model->cost_per_use,
                'external_job_id'   => (string) Str::uuid(),
            ]);

            // Log credit deduction in history
            CreditHistory::create([
                'user_id'           => $user->id,
                'amount'            => -$model->cost_per_use,
                'type'              => 'deduction',
                'description'       => "Image generation with {$model->name} (Job #{$job->id}).",
                'balance_before'    => $balanceBefore,
                'balance_after'     => $balanceAfter,
                'generation_job_id' => $job->id,
            ]);

            DB::commit();

            // Queue the background generation job
            \App\Jobs\ProcessImageGeneration::dispatch($job);

            return response()->json([
                'job_id'                  => $job->id,
                'status'                  => 'pending',
                'credits_remaining'       => [$model->slug => $creditBalance->credits_remaining],
                'estimated_wait_seconds'  => 30,
                'images_uploaded'         => count($sourceImagesUrls),
            ], 202);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to process request', 'detail' => $e->getMessage()], 500);
        }
    }
}
