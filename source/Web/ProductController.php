<?php

namespace Source\Web;

use Source\Models\Product;

class ProductController
{
    public function index(): void
    {
        $products = (new Product())->findAll();
        require ROOT_PATH . "/views/web/index.php";
    }
}
