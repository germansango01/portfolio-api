<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['title', 'slug', 'content', 'views', 'image_url', 'category_id', 'user_id'];

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
     * Scope a query to include common relationships for posts.
     * Selecting only necessary columns improves performance.
     */
    #[Scope]
    public function withRelations(Builder $query): void
    {
        $query->with(['user:id,name', 'category:id,name,slug', 'tags:id,name,slug']);
    }

    /**
     * Scope a query to search for a term in title or content.
     */
    #[Scope]
    public function searchTerm(Builder $query, ?string $term): void
    {
        $query->when($term, function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('content', 'like', "%{$term}%");
        });
    }

    /**
     * Scope a query to filter by category.
     */
    #[Scope]
    public function filterByCategory(Builder $query, ?int $categoryId): void
    {
        $query->when($categoryId, fn(Builder $q) => $q->where('category_id', $categoryId));
    }

    /**
     * Scope a query to filter by author.
     */
    #[Scope]
    public function filterByAuthor(Builder $query, ?int $authorId): void
    {
        $query->when($authorId, fn(Builder $q) => $q->where('user_id', $authorId));
    }

    /**
     * Scope a query to filter by tag.
     */
    #[Scope]
    public function filterByTag(Builder $query, ?int $tagId): void
    {
        $query->when($tagId, fn(Builder $q) => $q->whereHas('tags', fn(Builder $sub) => $sub->where('tags.id', $tagId)));
    }
}
