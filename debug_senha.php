<?php
require "vendor/autoload.php";

use Source\Models\User;

$user = new User();

if (!$user->findByEmail("pedro@teste.com")) {
    die("Usuário não encontrado");
}

echo "<pre>";
echo "Senha do banco:\n";
var_dump($user->getSenha());

echo "\npassword_verify:\n";
var_dump(password_verify("123", $user->getSenha()));
