<?php
session_start();

// Se já está logado, redirecionar para o dashboard
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['nivel'] >= 2) {
        header('Location: /rochas/app/');
    } else {
        header('Location: /rochas/');
    }
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/config/database.php';
    
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND ativo = 1");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario && password_verify($senha, $usuario['senha'])) {
                // Login bem-sucedido
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['nivel'] = $usuario['nivel'];
                
                // Redirecionar baseado no nível
                if ($usuario['nivel'] >= 2) {
                    header('Location: /rochas/app/');
                } else {
                    header('Location: /rochas/');
                }
                exit;
            } else {
                $erro = 'Email ou senha incorretos';
            }
        } catch (PDOException $e) {
            $erro = 'Erro no servidor. Tente novamente.';
            error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rochas E-commerce</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 420px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .login-header h1 {
            font-size: 28px;
            color: #111827;
            margin-bottom: 8px;
        }
        
        .login-header p {
            color: #6b7280;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #dc2626;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .login-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }
        
        .test-credentials {
            margin-top: 24px;
            padding: 16px;
            background: #f3f4f6;
            border-radius: 8px;
            font-size: 13px;
        }
        
        .test-credentials h4 {
            margin-bottom: 8px;
            color: #374151;
        }
        
        .test-credentials code {
            display: block;
            background: white;
            padding: 4px 8px;
            border-radius: 4px;
            margin: 4px 0;
            color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🏪 Rochas E-commerce</h1>
            <p>Faça login para acessar o painel administrativo</p>
        </div>
        
        <?php if ($erro): ?>
            <div class="error-message">
                ❌ <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    placeholder="seu@email.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="senha">Senha</label>
                <input 
                    type="password" 
                    id="senha" 
                    name="senha" 
                    required 
                    placeholder="Digite sua senha"
                >
            </div>
            
            <button type="submit" class="btn-login">
                Entrar
            </button>
        </form>
        
        <div class="test-credentials">
            <h4>📝 Credenciais de Teste:</h4>
            <code>admin@site.com</code>
            <code>Senha: admin123</code>
            <p style="margin-top: 8px; color: #6b7280; font-size: 12px;">
                (Você precisará atualizar a senha no banco)
            </p>
        </div>
        
        <div class="login-footer">
            © 2026 Rochas E-commerce
        </div>
    </div>
</body>
</html>