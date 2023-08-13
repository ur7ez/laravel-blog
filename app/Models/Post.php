<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @mixin Builder
 * @property mixed $id
 * @property string $title
 * @property string $body
 * @property string $slug
 * @property string $thumbnail
 * @property bool|int $active
 * @property Carbon $published_at
 */
class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'thumbnail',
        'body',
        'active',
        'published_at',
        'user_id',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_post', 'post_id', 'category_id');
    }

    /**
     * @param string $q search phrase
     * @param string $bgClass background color class, must be passed via Blade template in order to be picked up by Vite
     * @return string
     */
    public function titleHighlighted(string $q, string $bgClass = ''): string
    {
        return $this->_highlightPhrase($q, $this->title, $bgClass);
    }

    public function bodyHighlighted(string $q, string $bgClass = ''): string
    {
        return $this->_highlightPhrase($q, $this->body, $bgClass, true);
    }

    public function shortBody(int $words = 30): string
    {
        return Str::words(strip_tags($this->body), $words);
    }

    public function getFormattedDate()
    {
        return $this->published_at->format('F jS Y');
    }

    public function getThumbnail()
    {
        if (str_starts_with($this->thumbnail, 'http')) {
            return $this->thumbnail;
        }
        return '/storage/' . $this->thumbnail;
    }

    public function humanReadTime(): Attribute
    {
        return new Attribute(
            get: function ($value, $attributes) {
                $words = Str::wordCount(strip_tags($attributes['body']));
                $minutes = ceil($words / 200);

                return $minutes . ' ' . str('min')->plural($minutes) . ', '
                    . $words . ' ' . str('word')->plural($words);
            }
        );
    }

    /**
     * @param string $needle search phrase
     * @param string $source target string
     * @param string $bgClass background color class, must be passed via Blade template in order to be picked up by Vite
     * @param bool $wrapSource - set `true` to wrap long source text around
     * @return string|array|null
     */
    private function _highlightPhrase(string $needle, string $source, string $bgClass, bool $wrapSource = false): string|array|null
    {
        $needle = trim($needle);
        if (empty($bgClass) || empty($needle) || empty($source)) {
            return $source;
        }
        if ($wrapSource) {
            $source = $this->_extractTextSnippet($source, $needle);
        }
        $pattern = '/' . preg_quote($needle, '/') . '/i';
        return preg_replace($pattern, '<span class="' . $bgClass . '">$0</span>', $source);
    }

    /**
     * @param string $str target text phrase
     * @param string $q - pattern to be extracted
     * @param int $maxWords - maximum words to return in resulting context
     * @return string
     */
    private function _extractTextSnippet(string $str, string $q, int $maxWords = 30): string
    {
        $pattern = '/' . preg_quote($q, '/') . '/i';
        $str = strip_tags($str);

        if (preg_match($pattern, $str, $matches, PREG_OFFSET_CAPTURE)) {
            $startIndex = $matches[0][1];
            $contextStart = max(0, $startIndex - 20);
            $contextEnd = strlen($str);
//            $contextEnd = min(strlen($str), $startIndex + strlen($q) + 20);
            $context = substr($str, $contextStart, $contextEnd - $contextStart);
            if ($contextStart > 0) {
                $context = '...' . $context;
            }
        } else {
            $context = $str;
        }
        return Str::words($context, $maxWords);
    }
}
