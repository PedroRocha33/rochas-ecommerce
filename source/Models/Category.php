<?php

namespace Source\Models;

use Source\Core\Connect;
use Source\Core\Model;

class Category extends Model
{
    protected $id;
    protected $nome;
    protected $slug;
    protected $ativa;

    public function __construct(
        int $id = null,
        string $nome = null,
        string $slug = null,
        int $ativa = 1
    ) {
        $this->table = "categories";

        $this->id    = $id;
        $this->nome  = $nome;
        $this->slug  = $slug;
        $this->ativa = $ativa;
    }

    /* =========================
       GETTERS
    ========================= */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getAtiva(): ?int
    {
        return $this->ativa;
    }

    /* =========================
       BUSCAS
    ========================= */

    public function findById(int $id): bool
    {
        $stmt = Connect::getInstance()->prepare(
            "SELECT * FROM categories WHERE id = :id"
        );
        $stmt->bindValue(":id", $id);
        $stmt->execute();

        $result = $stmt->fetch();

        if (!$result) {
            return false;
        }

        $this->id    = $result->id;
        $this->nome  = $result->nome;
        $this->slug  = $result->slug;
        $this->ativa = $result->ativa;

        return true;
    }
}
