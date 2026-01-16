<?php

namespace Source\Models;

use Source\Core\Connect;
use Source\Core\Model;

class User extends Model
{
    protected $id;
    protected $nome;
    protected $email;
    protected $senha;
    protected $nivel;
    protected $ativo;
    protected $criado_em;

    public function __construct(
        int $id = null,
        string $nome = null,
        string $email = null,
        string $senha = null,
        int $nivel = null,
        int $ativo = null,
        string $criado_em = null
    ) {
        $this->table = "users";

        $this->id = $id;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->nivel = $nivel;
        $this->ativo = $ativo;
        $this->criado_em = $criado_em;
    }

    /* =========================
       GETTERS E SETTERS
    ========================= */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(?string $nome): void
    {
        $this->nome = $nome;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getSenha(): ?string
    {
        return $this->senha;
    }

    public function setSenha(?string $senha): void
    {
        $this->senha = $senha;
    }

    public function getNivel(): ?int
    {
        return $this->nivel;
    }

    public function setNivel(?int $nivel): void
    {
        $this->nivel = $nivel;
    }

    public function getAtivo(): ?int
    {
        return $this->ativo;
    }

    public function setAtivo(?int $ativo): void
    {
        $this->ativo = $ativo;
    }

    public function getCriadoEm(): ?string
    {
        return $this->criado_em;
    }

    /* =========================
       MÉTODOS DE NEGÓCIO
    ========================= */

    public function insert(): bool
    {
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errorMessage = "E-mail inválido";
            return false;
        }

        $conn = Connect::getInstance();

        // Verificar se email já existe
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindValue(":email", $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $this->errorMessage = "E-mail já cadastrado";
            return false;
        }

        // Criptografar senha
        $this->senha = password_hash($this->senha, PASSWORD_DEFAULT);

        return parent::insert();
    }

    public function findByEmail(string $email): bool
    {
        $stmt = Connect::getInstance()->prepare(
            "SELECT * FROM users WHERE email = :email"
        );
        $stmt->bindValue(":email", $email);
        $stmt->execute();

        $result = $stmt->fetch();

        if (!$result) {
            return false;
        }

        $this->id        = $result->id;
        $this->nome      = $result->nome;
        $this->email     = $result->email;
        $this->senha     = $result->senha;
        $this->nivel     = $result->nivel;
        $this->ativo     = $result->ativo;
        $this->criado_em = $result->criado_em;

        return true;
    }

    public function updatePassword(): bool
    {
        if (empty($this->id) || empty($this->senha)) {
            return false;
        }

        $conn = Connect::getInstance();
        $stmt = $conn->prepare(
            "UPDATE users SET senha = :senha WHERE id = :id"
        );

        $stmt->execute([
            ":senha" => password_hash($this->senha, PASSWORD_DEFAULT),
            ":id"    => $this->id
        ]);

        return $stmt->rowCount() > 0;
    }
}
