<?php 

namespace App\Repositories\Eloquent;

use App\Models\Review;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

class ReviewRepository extends BaseRepository implements ReviewRepositoryInterface
{
    public function getAllReviews()
    {
        return Review::with('user', 'product')->get();
    }

    public function getReviewById($id)
    {
        return Review::findOrFail($id);
    }

    public function createReview(array $data)
    {
        return Review::create($data);
    }

    public function updateReview($id, array $data)
    {
        $review = Review::findOrFail($id);
        $review->update($data);
        return $review;
    }

    public function deleteReview($id)
    {
        return Review::destroy($id);
    }
}
