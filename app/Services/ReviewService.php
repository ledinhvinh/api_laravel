<?php 

namespace App\Services;

use App\Repositories\Interfaces\ReviewRepositoryInterface;

class ReviewService
{
    protected $reviewRepository;

    public function __construct(ReviewRepositoryInterface $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    public function getAllReviews()
    {
        return $this->reviewRepository->getAllReviews();
    }

    public function createReview(array $data)
    {
        return $this->reviewRepository->createReview($data);
    }

    public function updateReview($id, array $data)
    {
        return $this->reviewRepository->updateReview($id, $data);
    }

    public function deleteReview($id)
    {
        return $this->reviewRepository->deleteReview($id);
    }
}
