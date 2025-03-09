<?php 
namespace App\Services;
use App\Repositories\Interfaces\CartRepositoryInterface;

class CartService
{
    protected $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function getCart($userId)
    {
        return $this->cartRepository->getUserCart($userId);
    }

    public function addItemToCart($userId, array $data)
    {
        return $this->cartRepository->addToCart($userId, $data);
    }

    public function updateCartItem($cartId, array $data)
    {
        return $this->cartRepository->updateCartItem($cartId, $data);
    }

    public function removeItemFromCart($cartId)
    {
        return $this->cartRepository->removeCartItem($cartId);
    }

    public function clearUserCart($userId)
    {
        return $this->cartRepository->clearCart($userId);
    }
}