<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Pendente</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
        }
        .icon { font-size: 80px; color: #ff9800; margin-bottom: 20px; }
        h1 { color: #333; margin-bottom: 10px; }
        p { color: #666; line-height: 1.6; }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #ff9800;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover { background: #f57c00; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">⏳</div>
        <h1>Pagamento Pendente</h1>
        <p>Estamos processando seu pagamento.</p>
        
        <?php if (isset($_GET['pedido_id'])): ?>
        <p><strong>Pedido:</strong> #<?= htmlspecialchars($_GET['pedido_id']) ?></p>
        <?php endif; ?>
        
        <p>Você receberá um e-mail com a confirmação.</p>
        <a href="/rochas" class="btn">Voltar</a>
    </div>
</body>
</html>
