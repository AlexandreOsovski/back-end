<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Models\ProductsModel;

class ProductService
{
    protected $repository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->repository = $productRepository;
    }

    public function all()
    {
        return $this->repository->all();
    }

    public function create(array $data): bool
    {
        $product = $this->repository->create(new ProductsModel(
            [
                "name" => $data["name"],
                "price" => $data["price"],
                "description" => $data["description"],
                "category" => $data["category"],
                "image_url" => $data["image_url"],
            ]
        ));
        return $product ? true : false;
    }

    public function find(string $column, string $data)
    {
        $result =  $this->repository->find($column, $data);
        if ($result) {
            return $result;
        }
        return null;
    }

    public function update(array $product, int $product_id): bool
    {
        $savedProduct = $this->repository->find('id', $product_id);

        $return = $this->repository->update($product_id, [
            "name" => $product["name"] == '' ? $savedProduct['name'] : $product['name'],
            "price" => $product["price"] == '' ? $savedProduct['price'] : $product['price'],
            "description" => $product["description"] == '' ? $savedProduct['description'] : $product['description'],
            "category" => $product["category"] == '' ? $savedProduct['category'] : $product['category'],
            "image_url" => $product["image_url"] == '' ? $savedProduct['image_url'] : $product['image_url'],
        ]);

        if ($return == true) {
            return true;
        }
        return false;
    }

    public function delete(int $id)
    {
        $this->repository->delete($id);
    }
}
