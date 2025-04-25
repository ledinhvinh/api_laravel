<?php

namespace App\Http\Controllers;
use App\Services\ProductService;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     protected $productRepository;
    public function __construct(ProductService $product)
    {
        $this->productRepository = $product;
    }
    public function index()
    {
        $data = $this->productRepository->getAll();
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $data = $request->validated();
        $product = $this->productRepository->create($data);
        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        return response()->json($this->productRepository->getById($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $data = $request->validated();
        $product = $this->productRepository->updateP($id, $data);
        return response()->json($product, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        return response()->json($this->productRepository->deleteP($id), 204);
    }
}
