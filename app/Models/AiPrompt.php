<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPrompt extends Model
{
    protected $fillable = [
        'category_id',
        'type_id',
        'ai_model_id',
        'image',
        'compressed_image',
        'images_data',
        'prompt',
        'slug',
        'description',
    ];

    protected $casts = [
        'images_data' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorite_prompts')->withTimestamps();
    }

}
