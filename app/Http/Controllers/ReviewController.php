<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $reviewer = Auth::user();
        $seller = $post->user;

        // if ($reviewer->id === $seller->id) {
        //     return response()->json([
        //         'message' => 'No puedes calificarte a ti mismo.'
        //     ], 422);
        // }

        $status = $request->rating < 3 ? 'negative' : 'confident';

        $comment = null;

        if ($status === 'negative') {
            $fullName = trim(($reviewer->first_name ?? '') . ' ' . ($reviewer->last_name ?? ''));

            if ($fullName === '') {
                $fullName = $reviewer->name ?? 'Usuario';
            }

            $comment = "{$fullName} otorgó {$request->rating} estrella(s) a este vendedor.";
        }

        $review = Review::updateOrCreate(
            [
                'user_id' => $reviewer->id,
                'seller_id' => $seller->id,
                'post_id' => $post->id,
            ],
            [
                'rating' => $request->rating,
                'status' => $status,
                'comment' => $comment,
            ]
        );

        $average = Review::where('seller_id', $seller->id)->avg('rating');
        $count = Review::where('seller_id', $seller->id)->count();

        return response()->json([
            'message' => 'Calificación guardada correctamente.',
            'review' => $review,
            'seller_rating_average' => round($average ?? 0, 1),
            'seller_rating_count' => $count,
            'status' => $status,
        ]);
    }
}