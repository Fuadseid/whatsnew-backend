<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Video extends Model
{
    /** @use HasFactory<\Database\Factories\VideoFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'video_url',
        'duration_seconds',
        'thumbnail_url',
        'is_premium',
        'credibility_score',
        'last_credibility_check',
        'status',
        'allow_likes',
        'allow_comments',
        'allow_shares'

        
    ];
    public function user():HasMany{
        return $this->hasMany(User::class);
    }
}
