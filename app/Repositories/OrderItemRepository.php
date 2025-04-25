<?php

namespace App\Repositories;

use App\Models\OrderItem;
use App\Repositories\Interfaces\BaseRepositoryInterface;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
class OrderItemRepository extends BaseRepository implements OrderItemRepositoryInterface
{
    public function getAll()
    {
        return OrderItem::all();
    }

    public function findById($id)
    {
        return OrderItem::find($id);
    }

    public function create(array $data)
    {
        return OrderItem::create($data);
    }

    public function update($id, array $data)
    {
        $orderItem = OrderItem::findOrFail($id);
        $orderItem->update($data);
        return $orderItem;
    }

    public function delete($id)
    {
        return OrderItem::destroy($id);
    }

    public function getByOrderId($orderId)
    {
        return OrderItem::where('order_id', $orderId)->get();
    }
    public function findByOrderId($orderId)
{
    return OrderItem::where('order_id', $orderId)->get();
}

}
