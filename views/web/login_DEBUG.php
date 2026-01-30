<?php
session_start();

// Carregar autoload
require __DIR__ . '/../../vendor/autoload.php';

// Carregar .env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
    $dotenv->load();
} catch (Exception $e) {
    // Ignorar
}

use Source\Core\Connect;

$debug_messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos';
        $debug_messages[] = "❌ Campos vazios";
    } else {
        try {
            $pdo = Connect::getInstance();
            $debug_messages[] = "✅ Conexão BD OK";
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND ativo = 1");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario) {
                $debug_messages[] = "✅ Usuário encontrado: " . $usuario['nome'];
                
                if (password_verify($senha, $usuario['senha'])) {
                    $debug_messages[] = "✅ Senha correta";
                    
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nome'] = $usuario['nome'];
                    $_SESSION['usuario_email'] = $usuario['email'];
                    $_SESSION['nivel'] = $usuario['nivel'];
                    
                    $debug_messages[] = "✅ Sessão criada";
                    $debug_messages[] = "📊 Nível: " . $usuario['nivel'];
                    
                    // NÃO REDIRECIONAR - apenas mostrar o que seria feito
                    // REDIRECIONAMENTO REAL (usando ROTAS)
                    if ($usuario['nivel'] >= 2) {
                        header("Location: http://localhost/rochas/app");
                    } else {
                        header("Location: http://localhost/rochas/");
                    }
                    exit;

                    
                    // MOSTRAR O DEBUG AO INVÉS DE REDIRECIONAR
                    $show_debug = true;
                } else {
                    $erro = 'Senha incorreta';
                    $debug_messages[] = "❌ Senha incorreta";
                }
            } else {
                $erro = 'Usuário não encontrado';
                $debug_messages[] = "❌ Usuário não encontrado ou inativo";
            }
        } catch (PDOException $e) {
            $erro = 'Erro no servidor';
            $debug_messages[] = "❌ Erro BD: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login • Rochas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a, #020617);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            color: #0f172a;
        }

        .login-container {
            background: #ffffff;
            width: 100%;
            max-width: 520px;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.35);
        }

        h1 {
            font-size: 26px;
            margin-bottom: 10px;
            color: #020617;
        }

        .subtitle {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 6px;
            color: #334155;
        }

        input {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            border: 1px solid #cbd5f5;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .alert {
            padding: 14px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 5px solid #dc2626;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 5px solid #22c55e;
        }

        .debug-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-top: 25px;
            border: 1px solid #e2e8f0;
        }

        .debug-box h3 {
            font-size: 15px;
            margin-bottom: 10px;
            color: #020617;
        }

        .debug-box pre {
            font-size: 13px;
            background: #ffffff;
            padding: 14px;
            border-radius: 8px;
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            line-height: 1.5;
        }

        .redirect-box {
            margin-top: 20px;
            padding: 20px;
            border-radius: 12px;
            background: #eff6ff;
            border-left: 5px solid #2563eb;
        }

        .redirect-box a {
            display: inline-block;
            margin-top: 10px;
            padding: 12px 20px;
            background: #2563eb;
            color: #fff;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
        }

        .redirect-box a:hover {
            background: #1d4ed8;
        }
    </style>
</head>

<body>

<div class="login-container">

    <h1>🔐 Login • Rochas</h1>
    <p class="subtitle">Área administrativa — modo debug ativo</p>

    <?php if (!empty($erro)): ?>
        <div class="alert alert-error">
            ❌ <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($show_debug) && $show_debug): ?>

        <div class="alert alert-success">
            ✅ Login realizado com sucesso
        </div>

        <div class="debug-box">
            <h3>📋 Log do processo</h3>
            <pre><?php foreach ($debug_messages as $msg) echo $msg . "\n"; ?></pre>
        </div>

        <div class="debug-box">
            <h3>💾 Sessão criada</h3>
            <pre><?php print_r($_SESSION); ?></pre>
        </div>

        <div class="redirect-box">
            <strong>🔄 Redirecionamento esperado:</strong><br>
            <?= $redirect_url ?>
            <br>
            <a href="<?= $redirect_url ?>" target="_blank">
                Ir para o painel
            </a>
        </div>

    <?php else: ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="senha" required>
            </div>

            <button class="btn">Entrar</button>
        </form>

    <?php endif; ?>

</div>

</body>
</html>
