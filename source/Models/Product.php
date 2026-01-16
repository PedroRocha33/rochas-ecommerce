<?php

namespace Source\Models;

use Source\Core\Connect;
use Source\Core\Model;
use PDO;
use PDOException;

class Product extends Model
{
     protected ?int $id = null;
    protected ?string $nome = null;
    protected ?string $slug = null;
    protected ?string $descricao = null;
    protected ?float $preco = null;
    protected ?int $estoque = null;
    protected ?string $imagem = null;
    protected ?int $categoria_id = null;
    protected ?bool $ativo = true;
    protected ?string $criado_em = null; // ✅ AQUI ESTÁ A CORREÇÃO

    public function __construct(
        int $id = null,
        string $nome = null,
        string $slug = null,
        string $descricao = null,
        float $preco = null,
        int $estoque = null,
        string $imagem = null,
        int $categoria_id = null,
        bool $ativo = true
    ) {
        $this->table = "products";

        $this->id = $id;
        $this->nome = $nome;
        $this->slug = $slug;
        $this->descricao = $descricao;
        $this->preco = $preco;
        $this->estoque = $estoque;
        $this->imagem = $imagem;
        $this->categoria_id = $categoria_id;
        $this->ativo = $ativo;
    }

    /* =====================
     * GETTERS & SETTERS
     * ===================== */

    public function getId(): ?int { return $this->id; }
    public function getNome(): ?string { return $this->nome; }
    public function getSlug(): ?string { return $this->slug; }
    public function getDescricao(): ?string { return $this->descricao; }
    public function getPreco(): ?float { return $this->preco; }
    public function getEstoque(): ?int { return $this->estoque; }
    public function getImagem(): ?string { return $this->imagem; }
    public function getCategoriaId(): ?int { return $this->categoria_id; }
    public function getAtivo(): ?bool { return $this->ativo; }

    public function setNome(string $nome): void { $this->nome = $nome; }
    public function setSlug(string $slug): void { $this->slug = $slug; }
    public function setDescricao(?string $descricao): void { $this->descricao = $descricao; }
    public function setPreco(float $preco): void { $this->preco = $preco; }
    public function setEstoque(int $estoque): void { $this->estoque = $estoque; }
    public function setImagem(?string $imagem): void { $this->imagem = $imagem; }
    public function setCategoriaId(int $categoria_id): void { $this->categoria_id = $categoria_id; }
    public function setAtivo(bool $ativo): void { $this->ativo = $ativo; }

    /* =====================
     * CRUD
     * ===================== */

    public function insert(): bool
    {
        if (empty($this->nome) || empty($this->preco)) {
            $this->errorMessage = "Nome e preço são obrigatórios";
            return false;
        }

        $this->slug = $this->slug ?? $this->generateSlug($this->nome);

        return parent::insert();
    }

    public function update(): bool
    {
        if (empty($this->id)) {
            $this->errorMessage = "ID não informado";
            return false;
        }

        return parent::update("id = :id", ["id" => $this->id]);
    }

    public function deleteById(int $id): bool
    {
        $stmt = Connect::getInstance()
            ->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute(["id" => $id]);
    }

    /* =====================
     * BUSCAS
     * ===================== */

    public function findById(int $id): bool
    {
        $stmt = Connect::getInstance()
            ->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
        $stmt->execute(["id" => $id]);

        if (!$result = $stmt->fetch(PDO::FETCH_OBJ)) {
            return false;
        }

        $this->map($result);
        return true;
    }

    public function findAll(array $filters = []): array
    {
        $sql = "SELECT * FROM products WHERE 1=1";
        $params = [];

        if (isset($filters['ativo'])) {
            $sql .= " AND ativo = :ativo";
            $params['ativo'] = $filters['ativo'];
        }

        if (!empty($filters['categoria_id'])) {
            $sql .= " AND categoria_id = :categoria";
            $params['categoria'] = $filters['categoria_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND nome LIKE :search";
            $params['search'] = "%{$filters['search']}%";
        }

        $stmt = Connect::getInstance()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function search(string $term): array
    {
        $stmt = Connect::getInstance()->prepare(
            "SELECT * FROM products 
             WHERE nome LIKE :t OR descricao LIKE :t"
        );
        $stmt->execute(["t" => "%{$term}%"]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findBySlug(string $slug): bool
    {
        $stmt = Connect::getInstance()
            ->prepare("SELECT * FROM products WHERE slug = :slug LIMIT 1");
        $stmt->execute(["slug" => $slug]);

        if (!$result = $stmt->fetch(PDO::FETCH_OBJ)) {
            return false;
        }

        $this->map($result);
        return true;
    }

    /* =====================
     * AUXILIARES
     * ===================== */

    private function map(object $data): void
    {
        $this->id = $data->id;
        $this->nome = $data->nome;
        $this->slug = $data->slug;
        $this->descricao = $data->descricao;
        $this->preco = (float) $data->preco;
        $this->estoque = (int) $data->estoque;
        $this->imagem = $data->imagem;
        $this->categoria_id = $data->categoria_id;
        $this->ativo = (bool) $data->ativo;
        $this->criado_em = $data->criado_em;
    }

    private function generateSlug(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }
}
