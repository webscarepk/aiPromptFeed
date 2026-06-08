<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GenerationJob extends Model
{
    protected $fillable = [
        'user_id',
        'model_id',
        'prompt',
        'source_image_url',
        'source_images_urls',
        'result_image_url',
        'status',
        'credits_consumed',
        'error_message',
        'external_job_id',
        'webhook_received_at'
    ];

    protected $casts = [
        'webhook_received_at' => 'datetime',
        'source_images_urls'  => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aiModel()
    {
        return $this->belongsTo(AiModel::class, 'model_id');
    }

    public function exploreFeedItem()
    {
        return $this->hasOne(ExploreFeedItem::class);
    }
}