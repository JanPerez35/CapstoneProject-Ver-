<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lending;
use App\Models\Post;
use App\Models\Review;

/**
 * Class ProfileController
 *
 * Handles all profile-related actions within the application.
 *
 * Responsibilities:
 * - displaying the authenticated user's profile
 * - retrieving the user's lending requests
 * - filtering lending requests by search and status
 * - retrieving the user's marketplace posts
 * - calculating seller rating statistics
 */
class ProfileController extends Controller
{
    /**
     * Displays the authenticated user's profile view.
     *
     * Retrieves the logged-in user information, their lending requests,
     * their marketplace posts, and their seller rating statistics.
     */
    public function profile(Request $request)
    {
        $user = auth()->user();

        // Get the user's lending requests
        $requestsQuery = Lending::with('items.equipment')
            ->where('user_id', $user->id);

        // Filter lending requests by equipment description
        if ($request->filled('request_search')) {
            $search = strtolower($request->request_search);

            $requestsQuery->where(function ($query) use ($search) {
                $query->whereHas('items.equipment', function ($q) use ($search) {
                    $q->whereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
                });
            });
        }

        // Filter lending requests by status
        if ($request->filled('request_status')) {
            $status = strtolower($request->request_status);

            if ($status === 'finished') {
                $requestsQuery->whereIn('status', ['finished', 'returned']);
            } else {
                $requestsQuery->where('status', $status);
            }
        }

        // Apply pagination to lending requests (5 requests per page)
        $requests = $requestsQuery
            ->orderByDesc('created_at')
            ->paginate(18)
            ->withQueryString();

        // Redirect to the first page if the current page exceeds the available pages
        if ($requests->currentPage() > $requests->lastPage() && $requests->lastPage() > 0) {
            return redirect()->route('my_profile', array_merge(
                $request->except('page'),
                ['tab' => 'requests', 'page' => 1]
            ));
        }

        // Get the user's posts from newest to oldest
        $posts = Post::where('user_id', $user->id)
            ->latest()
            ->get();

        // Get the user's average rating
        $sellerAverageRating = round(
            Review::where('seller_id', $user->id)->avg('rating') ?? 0,
            1
        );

        // Get the total number of reviews received by the user
        $sellerReviewsCount = Review::where('seller_id', $user->id)->count();

        // Return all profile data to the view
        return view('my_profile', compact(
            'user',
            'requests',
            'posts',
            'sellerAverageRating',
            'sellerReviewsCount'
        ));
    }
}
