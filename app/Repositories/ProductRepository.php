<?php

namespace App\Repositories;

use App\Repositories\Interfaces\ProductInterface;

use App\Models\ProductsModel;
use Illuminate\Database\Eloquent\Model;

class ProductRepository implements ProductInterface
{


    protected $model;

    public function __construct(ProductsModel $product)
    {
        $this->model = $product;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find($column, $data)
    {
        return $this->model->where($column, $data)->first();
    }

    public function create(ProductsModel $product): ProductsModel
    {
        $this->model->create($product->toArray());
        return $product;
    }


    public function update($id, array $attributes)
    {
        $product = $this->find('id', $id);
        $product->update($attributes);
        return $product;
    }

    public function delete($id)
    {
        $product = $this->find('id', $id);
        return $product->delete();
    }
}
