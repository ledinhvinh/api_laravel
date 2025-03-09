<?php 

namespace App\Services;

use App\Repositories\Interfaces\WishlistRepositoryInterface;

class WishlistService
{
    protected $wishlistRepository;

    public function __construct(WishlistRepositoryInterface $wishlistRepository)
    {
        $this->wishlistRepository = $wishlistRepository;
    }

    public function getUserWishlist($userId)
    {
        return $this->wishlistRepository->getUserWishlist($userId);
    }

    public function addToWishlist(array $data)
    {
        return $this->wishlistRepository->addToWishlist($data);
    }

    public function removeFromWishlist($id)
    {
        return $this->wishlistRepository->removeFromWishlist($id);
    }
}
