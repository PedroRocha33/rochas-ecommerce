<?php

namespace Source\Models;

use Source\Core\Connect;
use Source\Core\Model;
use PDO;
use PDOException;

class Order extends Model
{
    protected ?int $id = null;
    protected ?int $usuario_id = null;
    protected ?float $subtotal = null;
    protected ?float $total = null;
    protected ?string $status = null;
    protected ?string $criado_em = null;
    protected ?float $valor_frete = null;
    protected ?string $tipo_frete = null;
    protected ?int $prazo_frete = null;

    public function __construct(
        int $id = null,
        int $usuario_id = null,
        float $subtotal = null,
        float $total = null,
        string $status = "pending",
        float $valor_frete = null,
        string $tipo_frete = null,
        int $prazo_frete = null
    ) {
        $this->table = "orders";

        $this->id = $id;
        $this->usuario_id = $usuario_id;
        $this->subtotal = $subtotal;
        $this->total = $total;
        $this->status = $status;
        $this->valor_frete = $valor_frete;
        $this->tipo_frete = $tipo_frete;
        $this->prazo_frete = $prazo_frete;
    }

    /* =====================
     * GETTERS & SETTERS
     * ===================== */

    public function getId(): ?int { return $this->id; }
    public function getUsuarioId(): ?int { return $this->usuario_id; }
    public function getSubtotal(): ?float { return $this->subtotal; }
    public function getTotal(): ?float { return $this->total; }
    public function getStatus(): ?string { return $this->status; }
    public function getCriadoEm(): ?string { return $this->criado_em; }
    public function getValorFrete(): ?float { return $this->valor_frete; }
    public function getTipoFrete(): ?string { return $this->tipo_frete; }
    public function getPrazoFrete(): ?int { return $this->prazo_frete; }

    public function setUsuarioId(int $usuario_id): void { $this->usuario_id = $usuario_id; }
    public function setSubtotal(float $subtotal): void { $this->subtotal = $subtotal; }
    public function setTotal(float $total): void { $this->total = $total; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function setValorFrete(float $valor_frete): void { $this->valor_frete = $valor_frete; }
    public function setTipoFrete(string $tipo_frete): void { $this->tipo_frete = $tipo_frete; }
    public function setPrazoFrete(int $prazo_frete): void { $this->prazo_frete = $prazo_frete; }

    /* =====================
     * CRUD
     * ===================== */

    public function insert(): bool
    {
        if (empty($this->usuario_id) || empty($this->total)) {
            $this->errorMessage = "Usuário e total são obrigatórios";
            return false;
        }

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
            ->prepare("DELETE FROM orders WHERE id = :id");
        return $stmt->execute(["id" => $id]);
    }

    /* =====================
     * BUSCAS
     * ===================== */

    public function findById(int $id): bool
    {
        $stmt = Connect::getInstance()
            ->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
        $stmt->execute(["id" => $id]);

        if (!$result = $stmt->fetch(PDO::FETCH_OBJ)) {
            return false;
        }

        $this->map($result);
        return true;
    }

    public function findByUserId(int $userId): array
    {
        $stmt = Connect::getInstance()
            ->prepare("SELECT * FROM orders WHERE usuario_id = :uid ORDER BY criado_em DESC");
        $stmt->execute(["uid" => $userId]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /* =====================
     * AUXILIARES
     * ===================== */

    private function map(object $data): void
    {
        $this->id = $data->id;
        $this->usuario_id = (int) $data->usuario_id;
        $this->subtotal = (float) $data->subtotal;
        $this->total = (float) $data->total;
        $this->status = $data->status;
        $this->criado_em = $data->criado_em;
        $this->valor_frete = (float) $data->valor_frete;
        $this->tipo_frete = $data->tipo_frete;
        $this->prazo_frete = (int) $data->prazo_frete;
    }
}