<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Aprovado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
        }
        .icon { font-size: 80px; color: #4CAF50; margin-bottom: 20px; }
        h1 { color: #333; margin-bottom: 10px; }
        p { color: #666; line-height: 1.6; }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover { background: #764ba2; }
        .details {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">✓</div>
        <h1>Pagamento Aprovado!</h1>
        <p>Sua compra foi realizada com sucesso.</p>
        
        <?php if (isset($_GET['pedido_id'])): ?>
        <div class="details">
            <strong>Pedido:</strong> #<?= htmlspecialchars($_GET['pedido_id']) ?><br>
            <?php if (isset($_GET['payment_id'])): ?>
            <strong>Pagamento:</strong> <?= htmlspecialchars($_GET['payment_id']) ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <p>Você receberá um e-mail com os detalhes.</p>
        <a href="/rochas" class="btn">Voltar para a loja</a>
    </div>
</body>
</html>
