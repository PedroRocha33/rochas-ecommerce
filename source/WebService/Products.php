<?php

namespace Source\WebService;

use Source\Core\Connect;
use Source\Models\Product;
use Source\Models\Category;

class Products extends Api
{
    /**
     * LISTAR PRODUTOS
     * GET /api/products
     */
    public function listProducts(): void
    {
        $filters = [
            "search" => $_GET["search"] ?? null,
            "category_id" => $_GET["category_id"] ?? null,
            "ativo" => $_GET["ativo"] ?? 1
        ];

        $product = new Product();
        $products = $product->findAll($filters);

        $this->call(200, "success", "Lista de produtos", "success")
            ->back($products);
    }

    /**
     * CRIAR PRODUTO
     * POST /api/products
     */
   public function createProduct(array $data): void
{
    $this->auth();

    if (empty($data["nome"]) || empty($data["preco"])) {
        $this->call(400, "bad_request", "Nome e preço são obrigatórios", "error")->back();
        return;
    }

    // valida categoria
    if (!empty($data["categoria_id"])) {
        $category = new Category();
        if (!$category->findById($data["categoria_id"])) {
            $this->call(400, "bad_request", "Categoria inválida", "error")->back();
            return;
        }
    }

    // ===== UPLOAD DA IMAGEM =====
   $imagemPath = null;

if (!empty($_FILES["imagem"]) && $_FILES["imagem"]["error"] === UPLOAD_ERR_OK) {

    $uploadDir = dirname(__DIR__, 2) . "/storage/images";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = pathinfo($_FILES["imagem"]["name"], PATHINFO_EXTENSION);
    $nomeImagem = uniqid("prod_") . "." . $ext;

    $destino = $uploadDir . "/" . $nomeImagem;

    if (!move_uploaded_file($_FILES["imagem"]["tmp_name"], $destino)) {
        $this->call(500, "internal_server_error", "Erro ao salvar imagem", "error")->back();
        return;
    }

    $imagemPath = $nomeImagem;
}



    // ===== CRIA PRODUTO =====
    $product = new Product(
        null,
        $data["nome"],
        $data["slug"] ?? null,
        $data["descricao"] ?? null,
        (float) $data["preco"],
        (int) ($data["estoque"] ?? 0),
        $imagemPath,
        $data["categoria_id"] ?? null,
        $data["ativo"] ?? 1
    );

    if (!$product->insert()) {
        $this->call(500, "internal_server_error", $product->getErrorMessage(), "error")->back();
        return;
    }

    $this->call(201, "created", "Produto criado com sucesso", "success")->back([
        "id" => $product->getId(),
        "nome" => $product->getNome(),
        "imagem" => $imagemPath
    ]);
}


    /**
     * BUSCAR PRODUTO POR ID
     * GET /api/products/{id}
     */
    public function getProductById(array $data): void
    {
        if (empty($data["id"]) || !filter_var($data["id"], FILTER_VALIDATE_INT)) {
            $this->call(400, "bad_request", "ID inválido", "error")->back();
            return;
        }

        $product = new Product();
        if (!$product->findById($data["id"])) {
            $this->call(404, "not_found", "Produto não encontrado", "error")->back();
            return;
        }

        $this->call(200, "success", "Produto encontrado", "success")
            ->back([
                "id" => $product->getId(),
                "nome" => $product->getNome(),
                "slug" => $product->getSlug(),
                "descricao" => $product->getDescricao(),
                "preco" => $product->getPreco(),
                "estoque" => $product->getEstoque(),
                "imagem" => $product->getImagem(),
                "categoria_id" => $product->getCategoriaId(),
                "ativo" => $product->getAtivo()
            ]);
    }

    /**
     * ATUALIZAR PRODUTO
     * PUT /api/products/{id}
     */
    public function updateProduct(array $data): void
    {
        $this->auth();

        if (empty($data["id"])) {
            $this->call(400, "bad_request", "ID obrigatório", "error")->back();
            return;
        }

        $product = new Product();
        if (!$product->findById($data["id"])) {
            $this->call(404, "not_found", "Produto não encontrado", "error")->back();
            return;
        }

        if (isset($data["nome"])) $product->setNome($data["nome"]);
        if (isset($data["descricao"])) $product->setDescricao($data["descricao"]);
        if (isset($data["preco"])) $product->setPreco((float) $data["preco"]);
        if (isset($data["estoque"])) $product->setEstoque((int) $data["estoque"]);
        if (isset($data["categoria_id"])) $product->setCategoriaId((int) $data["categoria_id"]);
        if (isset($data["ativo"])) $product->setAtivo((bool) $data["ativo"]);

        if (!$product->update()) {
            $this->call(500, "internal_server_error", $product->getErrorMessage(), "error")->back();
            return;
        }

        $this->call(200, "success", "Produto atualizado com sucesso", "success")->back();
    }

    /**
     * DELETAR PRODUTO
     * DELETE /api/products/{id}
     */
    public function deleteProduct(array $data): void
    {
        $this->auth();

        if (empty($data["id"])) {
            $this->call(400, "bad_request", "ID obrigatório", "error")->back();
            return;
        }

        $product = new Product();
        if (!$product->findById($data["id"])) {
            $this->call(404, "not_found", "Produto não encontrado", "error")->back();
            return;
        }

        if (!$product->deleteById($data["id"])) {
            $this->call(500, "internal_server_error", "Erro ao excluir produto", "error")->back();
            return;
        }

        $this->call(200, "success", "Produto excluído com sucesso", "success")->back();
    }

    /**
     * BUSCAR PRODUTOS POR NOME
     * GET /api/products/search/{term}
     */
    public function searchProducts(array $data): void
    {
        if (empty($data["term"])) {
            $this->call(400, "bad_request", "Termo de busca obrigatório", "error")->back();
            return;
        }

        $product = new Product();
        $products = $product->search($data["term"]);

        $this->call(200, "success", "Resultado da busca", "success")->back($products);
    }
}
