<?php 

namespace App\Services;

use App\Repositories\Interfaces\ProductRepositoryInterface;


class ProductService
{
    protected $product;

    public function __construct(ProductRepositoryInterface $product)
    {
        $this->product = $product;
    }

    public function getAll()
    {
        return $this->product->getAll();
    }

    public function getById($id)
    {
        return $this->product->findById($id);
    }

    public function create(array $data)
    {
        
        return $this->product->create($data);
    }

    public function updateP($id, array $data)
    {
      
        return $this->product->update($id, $data);
    }

    public function deleteP($id)
    {
        return $this->product->delete($id);
    }
}
