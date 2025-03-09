<?php 
// app/Repositories/Interfaces/UserRepositoryInterface.php
namespace App\Repositories\Interfaces;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findUserById($id);
}
