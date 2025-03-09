<?php 
namespace App\Repositories\Interfaces;
interface CartRepositoryInterface extends BaseRepositoryInterface
{
    public function getUserCart($userId);
    public function addToCart($userId, array $data);
    public function updateCartItem($cartId, array $data);
    public function removeCartItem($cartId);
    public function clearCart($userId);
}