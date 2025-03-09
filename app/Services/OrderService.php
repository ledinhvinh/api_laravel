<?php

namespace App\Services;

use App\Repositories\OrderItemRepository;
use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;

class OrderService
{
    protected $orderRepository;
    protected $orderItemRepository;

    public function __construct(BaseRepositoryInterface $orderRepository, OrderItemRepository  $orderItemRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function createOrder($data)
    {
        try {
            // Tạo đơn hàng mới
            $order = Order::create([
                'user_id' => $data['user_id'],
                'order_date' => now(),
                'status' => 'pending',
                'total_price' => 0, // Sẽ cập nhật sau
            ]);
    
            $totalPrice = 0;
    
            // Duyệt qua từng sản phẩm trong đơn hàng
            $orderItems = collect($data['items'])->map(function ($item) use (&$totalPrice) {
                $totalPrice += $item['price'] * $item['quantity'];
                return new OrderItem([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            });
    
            // Sử dụng relationship để lưu danh sách OrderItem
            $order->orderItems()->saveMany($orderItems);
    
            // Cập nhật tổng giá trị đơn hàng
            $order->update(['total_price' => $totalPrice]);
    
            return $order;
        } catch (\Exception $e) {
            throw new \Exception("Failed to create order: " . $e->getMessage());
        }
    }

    public function updateOrderStatus($id, $status)
    {
        return $this->orderRepository->update($id, ['status' => $status]);
    }

    public function deleteOrder($id)
    {
        try {
            // Tìm Order theo ID
            $order = $this->orderRepository->findById($id);
    
            if (!$order) {
                throw new \Exception('Order not found');
            }
    
            // Xóa tất cả OrderItem liên quan
            $order->orderItems()->delete();
    
            // Xóa Order chính
            $order->delete();
        } catch (\Exception $e) {
            throw new \Exception("Failed to delete order: " . $e->getMessage());
        }
    }
}
