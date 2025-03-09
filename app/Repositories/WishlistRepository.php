<?php 

namespace App\Repositories\Eloquent;

use App\Models\Wishlist;
use App\Repositories\Interfaces\WishlistRepositoryInterface;

class WishlistRepository implements WishlistRepositoryInterface
{
    public function getUserWishlist($userId)
    {
        return Wishlist::where('user_id', $userId)->with('product')->get();
    }

    public function addToWishlist(array $data)
    {
        return Wishlist::create($data);
    }

    public function removeFromWishlist($id)
    {
        return Wishlist::destroy($id);
    }
}
