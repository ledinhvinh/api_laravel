<?php 
namespace App\Repositories\Interfaces;

interface WishlistRepositoryInterface
{
    public function getUserWishlist($userId);
    public function addToWishlist(array $data);
    public function removeFromWishlist($id);
}
