<?php

namespace App\Services;

use App\Repositories\OrderItemRepository;

class OrderItemService
{
    protected $orderItemRepository;

    public function __construct(OrderItemRepository $orderItemRepository)
    {
        $this->orderItemRepository = $orderItemRepository;
    }

    public function addOrderItem($data)
    {
        return $this->orderItemRepository->create($data);
    }

    public function updateOrderItem($id, $data)
    {
        return $this->orderItemRepository->update($id, $data);
    }

    public function deleteOrderItem($id)
    {
        return $this->orderItemRepository->delete($id);
    }
    public function getOrderItemsByOrderId($orderId)
{
    return $this->orderItemRepository->findByOrderId($orderId);
}
}
