<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Review;
use App\Models\UserReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Concerns\LogsActivity;

/**
 * Class ReviewController
 *
 * Handles all review-related actions within the application.
 *
 * Responsibilities:
 * - storing seller reviews
 * - validating review input
 * - preventing users from rating themselves
 * - calculating seller rating statistics
 * - generating automatic reports for low-rated sellers
 */
class ReviewController extends Controller
{
    use LogsActivity;

    /**
     * Stores or updates a review for a seller.
     *
     * Validates the rating value, verifies that the reviewer is not
     * rating themselves, and stores the review for the selected post.
     *
     * If the rating is below 2, the review is marked as negative
     * and a comment is generated automatically.
     *
     * If the seller average rating is 2.0 or lower, an automatic
     * report is created for administrative review.
     */
    public function store(Request $request, Post $post)
    {
        // Get the authenticated user and the seller of the post
        $reviewer = Auth::user();
        $seller = $post->user;

        // Prevent users from rating themselves
        if ($reviewer->id === $seller->id) {
            return response()->json([
                'message' => 'No puedes calificarte a ti mismo.'
            ], 422);
        }

        // Validate the incoming rating
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        // Determine the review status based on the rating
        $status = $request->rating < 2 ? 'negative' : 'confident';

        $comment = null;

        // Generate an automatic comment for negative reviews
        if ($status === 'negative') {
            $fullName = trim(($reviewer->first_name ?? '') . ' ' . ($reviewer->last_name ?? ''));

            if ($fullName === '') {
                $fullName = $reviewer->name ?? 'Usuario';
            }

            $comment = "{$fullName} otorgó {$request->rating} estrella(s) a este vendedor.";
        }

        // Store or update the review
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

        // Calculate the seller's average rating and total review count
        $average = Review::where('seller_id', $seller->id)->avg('rating');
        $count = Review::where('seller_id', $seller->id)->count();

        // Create an automatic report when the seller average is 2.0 or lower
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

        $sellerName = trim(($seller->first_name ?? '') . ' ' . ($seller->last_name ?? ''));
        if ($sellerName === '') {
            $sellerName = $seller->name ?? 'Usuario';
        }

        $this->logActivity(
            'Calificar usuario',
            "Se otorgó una calificación de {$request->rating} estrella(s) al vendedor '{$sellerName}' (ID: {$seller->id}) en la publicación ID: {$post->id}"
        );

        // Return the stored review data as JSON
        return response()->json([
            'message' => 'Calificación guardada correctamente.',
            'review' => $review,
            'seller_rating_average' => round($average ?? 0, 1),
            'seller_rating_count' => $count,
            'status' => $status,
        ]);
    }
}