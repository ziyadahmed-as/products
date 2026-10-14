<?php

namespace App\Observers;

use App\Models\ProductReview;
use App\Services\ActivityLogger;

class ProductReviewObserver
{
    public function created(ProductReview $review): void
    {
        $product = $review->product;
        ActivityLogger::log(
            module: 'Reviews',
            actionType: 'create',
            description: "Review submitted for '" . ($product?->name ?? 'Product') . "': Rating {$review->rating}/5 by " . ($review->reviewer_name ?? 'Customer') . ".",
            subject: $review,
            properties: [
                'review_id'       => $review->id,
                'product_id'      => $review->product_id,
                'order_reference' => $review->order_reference,
                'rating'          => $review->rating,
                'reviewer_name'   => $review->reviewer_name,
            ]
        );
    }

    public function updated(ProductReview $review): void
    {
        $dirty = $review->getDirty();
        unset($dirty['updated_at']);

        if (!empty($dirty)) {
            ActivityLogger::log(
                module: 'Reviews',
                actionType: 'update',
                description: "Review #{$review->id} for '" . ($review->product?->name ?? 'Product') . "' was updated.",
                subject: $review,
                properties: [
                    'review_id'  => $review->id,
                    'product_id' => $review->product_id,
                    'changes'    => $dirty,
                ]
            );
        }
    }
}
