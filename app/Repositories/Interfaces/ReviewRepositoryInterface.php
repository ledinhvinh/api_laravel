<?php 
namespace App\Repositories\Interfaces;

interface ReviewRepositoryInterface
{
    public function getAllReviews();
    public function getReviewById($id);
    public function createReview(array $data);
    public function updateReview($id, array $data);
    public function deleteReview($id);
}
