<?php

namespace App\Repositories\Interfaces;

use App\Models\ProductsModel;

interface ProductInterface
{
    public function all();
    public function find($column, $data);
    public function create(ProductsModel $product);
    public function update($id, array $attributes);
    public function delete($id);
}
