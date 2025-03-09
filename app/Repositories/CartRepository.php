<?php 
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Services\CartService;
use App\Repositories\BaseRepository;
use App\Models\Cart;

class CartRepository extends BaseRepository implements CartRepositoryInterface
{
    public function __contruct(Cart $cart)
    {
        parent::__construct($cart);
    }
    public function getUserCart($userId)
    {
        return Cart::where('user_id', $userId)->with('product')->get();
    }

    public function addToCart($userId, array $data)
    {
        return Cart::updateOrCreate(
            ['user_id' => $userId, 'product_id' => $data['product_id']],
            ['quantity' => $data['quantity']]
        );
    }

    public function updateCartItem($cartId, array $data)
    {
        $cart = Cart::findOrFail($cartId);
        $cart->update($data);
        return $cart;
    }

    public function removeCartItem($cartId)
    {
        return Cart::destroy($cartId);
    }

    public function clearCart($userId)
    {
        return Cart::where('user_id', $userId)->delete();
    }
}