<?php

namespace Source\WebService;

use Source\Core\Connect;
use Source\Models\User;
use Source\Core\JWTToken;

class Users extends Api
{
    /**
     * LISTAR USUÁRIOS
     */
    public function listUsers(): void
    {
        $users = new User();
        $this->call(200, "success", "Lista de usuários", "success")
            ->back($users->findAll());
    }

    /**
     * CRIAR USUÁRIO
     */
    public function createUser(array $data): void
    {
        if (empty($data["nome"]) || empty($data["email"]) || empty($data["senha"])) {
            $this->call(400, "bad_request", "Dados obrigatórios ausentes", "error")->back();
            return;
        }

        $pdo = Connect::getInstance();

        $nome  = filter_var($data["nome"], FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_var($data["email"], FILTER_VALIDATE_EMAIL);
        $senha = password_hash($data["senha"], PASSWORD_DEFAULT);
        $nivel = $data["nivel"] ?? 1;

        if (!$email) {
            $this->call(400, "bad_request", "E-mail inválido", "error")->back();
            return;
        }

        $stmt = $pdo->prepare("
            INSERT INTO users (nome, email, senha, nivel)
            VALUES (:nome, :email, :senha, :nivel)
        ");

        $stmt->execute([
            ":nome"  => $nome,
            ":email" => $email,
            ":senha" => $senha,
            ":nivel" => $nivel
        ]);

        $this->call(201, "created", "Usuário criado com sucesso", "success")->back();
    }

    /**
     * BUSCAR USUÁRIO POR ID
     */
    public function listUserById(array $data): void
    {
        if (empty($data["id"]) || !filter_var($data["id"], FILTER_VALIDATE_INT)) {
            $this->call(400, "bad_request", "ID inválido", "error")->back();
            return;
        }

        $user = new User();
        if (!$user->findById($data["id"])) {
            $this->call(404, "not_found", "Usuário não encontrado", "error")->back();
            return;
        }

        $this->call(200, "success", "Usuário encontrado", "success")->back([
            "id"    => $user->getId(),
            "nome"  => $user->getNome(),
            "email" => $user->getEmail(),
            "nivel" => $user->getNivel(),
            "ativo" => $user->getAtivo()
        ]);
    }

    /**
     * LOGIN
     */
   public function login(array $data): void
{
    if (empty($data["email"]) || empty($data["senha"])) {
        $this->call(400, "bad_request", "Credenciais inválidas", "error")->back();
        return;
    }

    $email = trim($data["email"]);
    $senha = trim($data["senha"]); // <<< AQUI ESTÁ A CHAVE

    $user = new User();

    if (!$user->findByEmail($email)) {
        $this->call(401, "unauthorized", "Usuário não encontrado", "error")->back();
        return;
    }

    if (!password_verify($senha, $user->getSenha())) {
        $this->call(401, "unauthorized", "Senha inválida", "error")->back();
        return;
    }

    if (!$user->getAtivo()) {
        $this->call(403, "forbidden", "Usuário inativo", "error")->back();
        return;
    }

    $jwt = new JWTToken();
    $token = $jwt->create([
        "id"    => $user->getId(),
        "nome"  => $user->getNome(),
        "email" => $user->getEmail(),
        "nivel" => $user->getNivel()
    ]);

    $this->call(200, "success", "Login realizado com sucesso", "success")->back([
        "token" => $token,
        "user" => [
            "id"    => $user->getId(),
            "nome"  => $user->getNome(),
            "email" => $user->getEmail(),
            "nivel" => $user->getNivel()
        ]
    ]);
}

    /**
     * EXCLUIR USUÁRIO
     */
    public function deleteUser(array $data): void
    {
        $this->auth();

        if (empty($data["id"])) {
            $this->call(400, "bad_request", "ID obrigatório", "error")->back();
            return;
        }

        $user = new User();
        if (!$user->findById($data["id"])) {
            $this->call(404, "not_found", "Usuário não encontrado", "error")->back();
            return;
        }

        $user->delete();

        $this->call(200, "success", "Usuário excluído com sucesso", "success")->back();
    }
}
