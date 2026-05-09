<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;

/**
 * Class PruneOldPosts
 *
 * Deletes posts older than a configured time window.
 *
 * Responsibilities:
 * - find expired posts
 * - delete associated images
 * - delete post records
 */
class PruneOldPosts extends Command
{
    protected $signature = 'data:prune-old-posts';
    protected $description = 'Delete posts older than 15 days';

    public function handle()
    {
        $expiration = now()->subDays(15);
        
        $posts = Post::where('created_at', '<=', $expiration)->get();

        foreach ($posts as $post) {
            foreach ([
                $post->photo_1_url,
                $post->photo_2_url,
                $post->photo_3_url
            ] as $img) {
                if ($img) {
                    Storage::disk('public')->delete($img);
                }
            }

            $post->delete();
        }

        $this->info("Deleted {$posts->count()} posts older than 15 days.");

        return Command::SUCCESS;
    }
}