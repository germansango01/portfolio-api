<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'content',
    ];

    /**
     * Get the user that owns the post.
     */
    public function user(): BelongsTo
    {
    return $this->belongsTo(User::class);
    }

    /**
     * Get the category that owns the post.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The tags that belong to the post.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Get the comments for the post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /* === Scopes reutilizables === */
    public function scopeWithRelations($query)
    {
        return $query->select(['id', 'title', 'slug', 'content', 'image_url', 'views', 'category_id', 'user_id', 'created_at'])
            ->withCount('comments')
            ->with([
                'category:id,name,slug',
                'tags:id,name,slug',
                'user:id,name',
            ]);
    }

    /** Scope to order posts by latest created. */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /*
    * Search posts by a given term in title, content, category name, tag name, or author name.
    */
    public function scopeSearchTerm($query, ?string $term)
    {
        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('title', 'LIKE', "%{$term}%")
                  ->orWhere('content', 'LIKE', "%{$term}%")
                  ->orWhereHas('category', fn ($q) => $q->where('name', 'LIKE', "%{$term}%"))
                  ->orWhereHas('tags', fn ($q) => $q->where('name', 'LIKE', "%{$term}%"))
                  ->orWhereHas('user', fn ($q) => $q->where('name', 'LIKE', "%{$term}%"));
            });
        }

        return $query;
    }

    /** Filter posts by a given category ID. */
    public function scopeFilterByCategory($query, ?int $categoryId)
    {
        return $categoryId ? $query->where('category_id', $categoryId) : $query;
    }

    /** Filter posts by a given author ID. */
    public function scopeFilterByAuthor($query, ?int $authorId)
    {
        return $authorId ? $query->where('user_id', $authorId) : $query;
    }

    /** Filter posts by a given tag ID. */
    public function scopeFilterByTag($query, ?int $tagId)
    {
        return $tagId ? $query->whereHas('tags', fn ($q) => $q->where('tags.id', $tagId)) : $query;
    }
}
