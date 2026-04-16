<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Review;
use App\Models\User;
use App\Models\UserReport;
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

        $status = $request->rating < 2 ? 'negative' : 'confident';

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

        // Auto-report when seller average is 2.0 or lower
        if ($average !== null && $average <= 2) {
            UserReport::updateOrCreate(
                [
                    'reported_user_id' => $seller->id,
                    'report_reason' => 'Calificación baja',
                    'status' => 'pending',
                ],
                [
                    'user_id' => $reviewer->id,
                    'post_id' => $post->id,
                    'description' => 'El usuario tiene 2 estrellas o menos en promedio, lo que puede indicar una posible estafa o comportamiento indebido.',
                    'resolved_at' => null,
                ]
            );
        }

        return response()->json([
            'message' => 'Calificación guardada correctamente.',
            'review' => $review,
            'seller_rating_average' => round($average ?? 0, 1),
            'seller_rating_count' => $count,
            'status' => $status,
        ]);
    }
}