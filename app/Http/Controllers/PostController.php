<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Review;
use App\Models\User;
use App\Http\Controllers\Concerns\LogsActivity;

/**
 * Class PostController
 *
 * Handles marketplace post management.
 *
 * Responsibilities:
 * - creating posts
 * - deleting posts
 * - listing posts
 * - retrieving post details
 * - enforcing daily post limits
 */
class PostController extends Controller
{
    use LogsActivity;

    /**
     * Stores a new marketplace post.
     *
     * Validates:
     * - title, cost, category, condition
     * - optional images (max 3)
     * 
     * Enforces:
     * - daily post limit per user
     *
     * Handles:
     * - image upload to storage
     * - saving post data
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|min:5|max:100',
            'description' => 'nullable|max:500',
            'cost' => 'required|numeric',
            'category' => 'required',
            'condition' => 'required',
            'images' => 'nullable|array|max:3',
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $postLimitData = $this->getPostLimitData(auth()->id());

        if ($postLimitData['limit_reached']) {
            return response()->json([
                'message' => 'Límite alcanzado'
            ], 403);
        }

        $paths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $paths[] = $image->store('posts', 'public');
            }
        }

        $post = Post::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'cost' => $request->cost,
            'category' => $request->category,
            'condition' => $request->condition,
            'status' => 'Disponible',
            'photo_1_url' => $paths[0] ?? null,
            'photo_2_url' => $paths[1] ?? null,
            'photo_3_url' => $paths[2] ?? null,
        ]);

        $this->logActivity(
            'Crear publicación',
            "Se creó la publicación: {$post->title} (ID: {$post->id})"
        );

        return response()->json([
            'success' => true,
            'post' => $post
            ], 200);
    }
    /**
     * Deletes a post.
     *
     * Authorization:
     * - owner of the post
     * - Super Administrador
     * - Administrador de Mercado
     *
     * Actions:
     * - deletes associated images
     * - deletes post record
     */
    public function destroy(Post $post)
    {
        if ($post->user_id !== auth()->id() && !in_array(auth()->user()->role, ['Super Administrador', 'Administrador de Mercado'])) {
            abort(403);
        }
        $images = [
                $post->photo_1_url,
                $post->photo_2_url,
                $post->photo_3_url
        ];

        foreach ($images as $image) {
            if ($image) {
                Storage::disk('public', 'posts')->delete($image);
            }
        }

        $postTitle = $post->title;
        $postId = $post->id;

        $post->delete();

        $this->logActivity(
            'Eliminar publicación',
            "Se eliminó la publicación: {$postTitle} (ID: {$postId})"
        );

        return response()->json([
            'success' => true,
            'message' => 'Post eliminado exitosamente'
            ]);
    }
    /**
     * Displays all posts (view).
     *
     * Returns:
     * - latest posts ordered by creation date
     */
    public function index()
    {
        $posts = Post::latest()->get();
        return view('kinemarket', compact('posts'));
    }
    /**
     * Returns all posts in JSON format.
     *
     * Includes:
     * - seller info
     * - rating average
     * - reviews count
     * - formatted time (time ago)
     */
    public function getPosts()
    {
        $posts = Post::with('user')
            ->whereHas('user', function ($query) {
                    $query->where('status', 'Activo');
                })
            ->latest()
            ->get()
            ->map(function ($post) {
                $averageRating = Review::where('seller_id', $post->user_id)->avg('rating');
                $reviewsCount = Review::where('seller_id', $post->user_id)->count();

                $sellerName = trim(($post->user->first_name ?? '') . ' ' . ($post->user->last_name ?? ''));
                if ($sellerName === '') {
                    $sellerName = $post->user->name ?? 'Usuario';
                }

                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'description' => $post->description,
                    'cost' => $post->cost,
                    'category' => $post->category,
                    'condition' => $post->condition,
                    'status' => $post->status,
                    'photo_1_url' => $post->photo_1_url,
                    'photo_2_url' => $post->photo_2_url,
                    'photo_3_url' => $post->photo_3_url,
                    'created_at' => $post->created_at?->toISOString(),
                    'time_ago' => $post->created_at?->diffForHumans(),
                    'rating' => round($averageRating ?? 0, 1),
                    'reviews' => $reviewsCount,
                    'user' => [
                        'id' => $post->user->id,
                        'name' => $sellerName,
                        'first_name' => $post->user->first_name ?? '',
                        'last_name' => $post->user->last_name ?? '',
                        'average_rating' => round($averageRating ?? 0, 1),
                        'reviews_count' => $reviewsCount,
                    ],
                ];
            });

        return response()->json($posts);
    }
    /**
     * Retrieves a single post by ID.
     *
     * Includes:
     * - seller info
     * - rating average
     * - reviews count
     */
    public function show($id)
    {
        $post = Post::with('user')->findOrFail($id);

        $averageRating = round(
            Review::where('seller_id', $post->user->id)->avg('rating') ?? 0,
            1
        );

        $reviewsCount = Review::where('seller_id', $post->user->id)->count();

        $sellerName = trim(($post->user->first_name ?? '') . ' ' . ($post->user->last_name ?? ''));
        if ($sellerName === '') {
            $sellerName = $post->user->name ?? 'Usuario';
        }

        return response()->json([
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'cost' => $post->cost,
            'status' => $post->status,
            'condition' => $post->condition,
            'category' => $post->category,
            'photo_1_url' => $post->photo_1_url,
            'photo_2_url' => $post->photo_2_url,
            'photo_3_url' => $post->photo_3_url,
            'rating' => $averageRating,
            'reviews' => $reviewsCount,
            'user' => [
                'id' => $post->user->id,
                'name' => $sellerName,
                'first_name' => $post->user->first_name ?? '',
                'last_name' => $post->user->last_name ?? '',
                'average_rating' => $averageRating,
                'reviews_count' => $reviewsCount,
            ]
        ]);
    }

    /**
     * Returns post limit info for frontend.
     */
    public function getUserPostLimit()
    {
        return response()->json(
            $this->getPostLimitData(auth()->id())
        );
    }

    /**
     * CENTRALIZED LOGIC
     *
     * Calculates:
     * - posts in last 24h
     * - max allowed
     * - remaining posts
     * - if limit reached
     */
    private function getPostLimitData($userId)
    {
        $postsLast15Days = Post::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(15))
            ->count();

        $maxPosts = 15;

        return [
            'posts_last_15_days' => $postsLast15Days,
            'max_posts' => $maxPosts,
            'remaining_posts' => max(0, $maxPosts - $postsLast15Days),
            'limit_reached' => $postsLast15Days >= $maxPosts,
        ];
    }
}
