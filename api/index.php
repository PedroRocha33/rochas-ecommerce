<?php

ob_start();

require  __DIR__ . "/../vendor/autoload.php";

// os headers abaixo são necessários para permitir o acesso a API por clientes externos ao domínio
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header('Access-Control-Allow-Credentials: true'); // Permitir credenciais

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

use CoffeeCode\Router\Router;

$route = new Router("http://localhost/rochas/api",":");

$route->namespace("Source\WebService");

/* USERS */
$route->group("/users");

$route->post("/login", "Users:login");
$route->post("/photo", "Users:updatePhoto");
//http://localhost:8080/inf-3at-2025/api/users/
$route->get("/", "Users:listUsers");
//http://localhost:8080/inf-3at-2025/api/users/id/2
$route->get("/id/{id}", "Users:listUserById");
$route->get("/id/", "Users:listUserById");
//http://localhost:8080/inf-3at-2025/api/users/add
$route->post("/add", "Users:createUser");
//http://localhost:8080/inf-3at-2025/api/users/update
$route->put("/update/id/{id}", "Users:updateUser");
//http://localhost:8080/inf-3at-2025/api/users/delete/id/38
$route->delete("/delete/id/{id}", "Users:deleteUser");

/* PRODUCTS */
$route->group("/products");

// Listar produtos (com filtros opcionais via query string)
// GET http://localhost:8080/inf-3at-2025/api/products/
// GET http://localhost:8080/inf-3at-2025/api/products/?categoria_id=1&page=2&limit=10
$route->get("/", "Products:listProducts");

// Buscar produto por ID
// GET http://localhost:8080/inf-3at-2025/api/products/id/15
$route->get("/id/{id}", "Products:getProductById");
$route->get("/id/", "Products:getProductById");

// Criar novo produto
// POST http://localhost:8080/inf-3at-2025/api/products/add
$route->post("/add", "Products:createProduct");

// Atualizar produto
// PUT http://localhost:8080/inf-3at-2025/api/products/update/id/15
$route->put("/update/id/{id}", "Products:updateProduct");

// Excluir produto
// DELETE http://localhost:8080/inf-3at-2025/api/products/delete/id/15
$route->delete("/delete/id/{id}", "Products:deleteProduct");

// Buscar produtos (search)
// POST http://localhost:8080/inf-3at-2025/api/products/search
$route->post("/search", "Products:searchProducts");

// Produtos com estoque baixo
// GET http://localhost:8080/inf-3at-2025/api/products/low-stock
$route->get("/low-stock", "Products:getLowStockProducts");

// Informações de estoque de um produto
// GET http://localhost:8080/inf-3at-2025/api/products/stock/id/15
$route->get("/stock/id/{id}", "Products:getProductStock");

// Ativar/Desativar produto
// PUT http://localhost:8080/inf-3at-2025/api/products/toggle-status/id/15
$route->put("/toggle-status/id/{id}", "Products:toggleProductStatus");

/* CATEGORIES (opcional - para suporte aos produtos) */
$route->group("/categories");

// Listar categorias
// GET http://localhost:8080/inf-3at-2025/api/categories/
$route->get("/", "Categories:listCategories");

// Buscar categoria por ID
// GET http://localhost:8080/inf-3at-2025/api/categories/id/5
$route->get("/id/{id}", "Categories:getCategoryById");

// Criar categoria
// POST http://localhost:8080/inf-3at-2025/api/categories/add
$route->post("/add", "Categories:createCategory");

// Atualizar categoria
// PUT http://localhost:8080/inf-3at-2025/api/categories/update/id/5
$route->put("/update/id/{id}", "Categories:updateCategory");

// Excluir categoria
// DELETE http://localhost:8080/inf-3at-2025/api/categories/delete/id/5
$route->delete("/delete/id/{id}", "Categories:deleteCategory");

$route->group("/recovery-password");
$route->post("/add", "Products:createProduct");


$route->group("null");

$route->dispatch();

/** ERROR REDIRECT */
if ($route->error()) {
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code(404);

    echo json_encode([
        "code" => 404,
        "status" => "not_found",
        "message" => "URL não encontrada"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

}

ob_end_flush();