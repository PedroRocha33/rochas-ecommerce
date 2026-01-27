<?php
/**
 * Script para criar uma sessão de teste
 * Execute este arquivo e depois acesse o dashboard
 */

session_start();

// Simular login de administrador
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nome'] = 'Administrador';
$_SESSION['usuario_email'] = 'admin@site.com';
$_SESSION['nivel'] = 3; // Administrador

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessão Configurada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 100px auto;
            padding: 20px;
            text-align: center;
        }
        .success {
            background: #d4edda;
            border: 2px solid #28a745;
            padding: 30px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .success h1 {
            color: #155724;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 10px;
        }
        .info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
        code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="success">
        <h1>✅ Sessão Configurada com Sucesso!</h1>
        <p>Sua sessão foi criada como <strong>Administrador</strong></p>
    </div>

    <div class="info">
        <h3>📋 Informações da Sessão:</h3>
        <ul>
            <li><strong>ID:</strong> <?= $_SESSION['usuario_id'] ?></li>
            <li><strong>Nome:</strong> <?= $_SESSION['usuario_nome'] ?></li>
            <li><strong>Email:</strong> <?= $_SESSION['usuario_email'] ?></li>
            <li><strong>Nível:</strong> <?= $_SESSION['nivel'] ?> (Administrador)</li>
        </ul>
    </div>

    <a href="/rochas/app/" class="btn">🚀 Acessar Dashboard</a>
    <a href="/rochas/login.php" class="btn" style="background: #6c757d;">🔒 Fazer Login Normal</a>

    <p style="margin-top: 40px; color: #6c757d; font-size: 14px;">
        ⚠️ <strong>Importante:</strong> Delete este arquivo após usar!
    </p>
</body>
</html>