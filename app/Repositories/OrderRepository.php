<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\BaseRepositoryInterface;

class OrderRepository implements BaseRepositoryInterface
{
    public function getAll()
    {
        return Order::all();
    }

    public function findById($id)
    {
        return Order::with('orderItems')->find($id);
    }

    public function create(array $data)
    {
        return Order::create($data);
    }

    public function update($id, array $data)
    {
        $order = Order::findOrFail($id);
        $order->update($data);
        return $order;
    }

    public function delete($id)
    {
        return Order::destroy($id);
    }
}
