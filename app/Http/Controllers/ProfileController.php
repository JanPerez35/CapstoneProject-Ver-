<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lending;
use App\Models\Post;
use App\Models\Review;

class ProfileController extends Controller
{
    public function profile(Request $request)
    {
        $user = auth()->user();

        $requestsQuery = Lending::with('items.equipment')
            ->where('user_id', $user->id);

        if ($request->filled('request_search')) {
            $search = strtolower($request->request_search);

            $requestsQuery->where(function ($query) use ($search) {
                $query->whereHas('items.equipment', function ($q) use ($search) {
                    $q->whereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
                });
            });
        }

        if ($request->filled('request_status')) {
            $status = strtolower($request->request_status);

            if ($status === 'finished') {
                $requestsQuery->whereIn('status', ['finished', 'returned']);
            } else {
                $requestsQuery->where('status', $status);
            }
        }

        $requests = $requestsQuery
            ->latest('created_at')
            ->paginate(5)
            ->withQueryString();

        if ($requests->currentPage() > $requests->lastPage() && $requests->lastPage() > 0) {
            return redirect()->route('my_profile', array_merge(
                $request->except('page'),
                ['tab' => 'requests', 'page' => 1]
            ));
        }

        $posts = Post::where('user_id', $user->id)
            ->latest()
            ->get();

        $sellerAverageRating = round(
            Review::where('seller_id', $user->id)->avg('rating') ?? 0,
            1
        );

        $sellerReviewsCount = Review::where('seller_id', $user->id)->count();

        return view('my_profile', compact(
            'user',
            'requests',
            'posts',
            'sellerAverageRating',
            'sellerReviewsCount'
        ));
    }
}