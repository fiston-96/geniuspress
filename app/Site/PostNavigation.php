<?php

namespace App\Site;

use App\Models\Post;

/** Article précédent et suivant (même langue, par date de publication). */
final class PostNavigation
{
    /** @return array{previous: ?Post, next: ?Post} */
    public static function for(Post $post): array
    {
        $base = fn () => Post::query()->published()->where('locale', $post->locale)->whereKeyNot($post->id);
        $date = $post->published_at ?? $post->created_at;

        return [
            'previous' => $base()->where(fn ($q) => $q->where('published_at', '<', $date)->orWhere(fn ($q) => $q->where('published_at', $date)->where('id', '<', $post->id)))
                ->orderByDesc('published_at')->orderByDesc('id')->first(),
            'next' => $base()->where(fn ($q) => $q->where('published_at', '>', $date)->orWhere(fn ($q) => $q->where('published_at', $date)->where('id', '>', $post->id)))
                ->orderBy('published_at')->orderBy('id')->first(),
        ];
    }
}
