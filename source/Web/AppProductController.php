<?php

namespace Source\Web;

use Source\Models\Product;

class AppProductController
{
    public function index(): void
    {
        $product = new Product();
        $products = $product->all();

        $title = "Produtos";
        $view  = ROOT_PATH . "/views/app/products/index.php";

        require ROOT_PATH . "/views/app/layout.php";
    }

    public function create(): void
    {
        if ($_POST) {
            (new Product())->create($_POST);
            header("Location: /rochas/app/products");
            exit;
        }

        $title = "Novo Produto";
        $view  = ROOT_PATH . "/views/app/products/create.php";

        require ROOT_PATH . "/views/app/layout.php";
    }

    public function edit(array $data): void
    {
        $product = new Product();

        if ($_POST) {
            $product->update($data['id'], $_POST);
            header("Location: /rochas/app/products");
            exit;
        }

        $item  = $product->find($data['id']);
        $title = "Editar Produto";
        $view  = ROOT_PATH . "/views/app/products/edit.php";

        require ROOT_PATH . "/views/app/layout.php";
    }

    public function delete(array $data): void
    {
        (new Product())->delete($data['id']);
        header("Location: /rochas/app/products");
        exit;
    }
}
