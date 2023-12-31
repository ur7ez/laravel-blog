<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug'];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'category_post', 'category_id', 'post_id');
    }

    public function publishedPosts(int $limit = 3): BelongsToMany
    {
        return $this->posts()
            ->where('active', '=', 1)
            ->whereDate('published_at', '<=', Carbon::now())
            ->limit($limit);
    }
}
