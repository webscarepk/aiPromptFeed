<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExploreFeedItem extends Model
{
    protected $fillable = [
        'generation_job_id',
        'model_id',
        'prompt',
        'image_url',
        'is_featured',
        'display_order'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    public function generationJob()
    {
        return $this->belongsTo(GenerationJob::class);
    }

    public function aiModel()
    {
        return $this->belongsTo(AiModel::class, 'model_id');
    }
}