<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPrompt extends Model
{
    protected $fillable = [
        'category_id',
        'type_id',
        'image',
        'prompt',
        'slug',
        'description',
        'compressed_image'
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
