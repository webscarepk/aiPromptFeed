<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
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
            'prompt' => 'required|string|max:1000',
            'image' => 'nullable|file|mimes:jpeg,png|max:10240',
        ]);

        $user = $request->user();
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
                    'error' => 'INSUFFICIENT_CREDITS',
                    'model_name' => $model->name,
                    'credits_remaining' => $creditBalance ? $creditBalance->credits_remaining : 0,
                    'credits_required' => $model->cost_per_use,
                    'upgrade_url' => url('/plans')
                ], 402);
            }

            // Deduct credits
            $creditBalance->credits_remaining -= $model->cost_per_use;
            $creditBalance->save();

            // Handle image upload if present
            $sourceImageUrl = null;
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('generations/source', 'public');
                $sourceImageUrl = asset('storage/' . $path);
            }

            // Create job
            $job = GenerationJob::create([
                'user_id' => $user->id,
                'model_id' => $model->id,
                'prompt' => $request->prompt,
                'source_image_url' => $sourceImageUrl,
                'status' => 'pending',
                'credits_consumed' => $model->cost_per_use,
                'external_job_id' => (string) Str::uuid(), // mock for now
            ]);

            DB::commit();

            // Queue job here: Dispatch worker job
            \App\Jobs\ProcessImageGeneration::dispatch($job);

            return response()->json([
                'job_id' => $job->id,
                'status' => 'pending',
                'credits_remaining' => [$model->slug => $creditBalance->credits_remaining],
                'estimated_wait_seconds' => 30
            ], 202);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to process request'], 500);
        }
    }
}
