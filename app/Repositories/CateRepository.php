<?php 
// app/Repositories/UserRepository.php
namespace App\Repositories;

use App\Models\Category;

use App\Repositories\Interfaces\CateRepositoryInterface;

class CateRepository extends BaseRepository implements CateRepositoryInterface
{
    public function __construct(Category $category)
    {
        parent::__construct($category);
    }

    
}
