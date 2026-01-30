<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Cancelado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
        }
        .icon { font-size: 80px; color: #f5576c; margin-bottom: 20px; }
        h1 { color: #333; margin-bottom: 10px; }
        p { color: #666; line-height: 1.6; }
        .btn {
            display: inline-block;
            margin: 10px 5px;
            padding: 12px 30px;
            background: #f5576c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover { background: #d4405b; }
        .btn-secondary { background: #666; }
        .btn-secondary:hover { background: #444; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">✕</div>
        <h1>Pagamento Cancelado</h1>
        <p>Não foi possível processar o pagamento.</p>
        
        <?php if (isset($_GET['pedido_id'])): ?>
        <p><strong>Pedido:</strong> #<?= htmlspecialchars($_GET['pedido_id']) ?></p>
        <?php endif; ?>
        
        <p>Você pode tentar novamente.</p>
        <a href="/rochas/checkout" class="btn">Tentar Novamente</a>
        <a href="/rochas" class="btn btn-secondary">Voltar</a>
    </div>
</body>
</html>
