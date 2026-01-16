<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Lista de Produtos</title>
    <link rel="stylesheet" href="/assets/css/_theme.css">
</head>
<body>

<h1>Lista de Produtos</h1>

<div class="products-grid">
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
            <div class="product-card">

                <?php if (!empty($product->imagem)): ?>
                    <img
                        src="/assets/img/<?= htmlspecialchars($product->imagem) ?>"
                        alt="<?= htmlspecialchars($product->nome) ?>"
                        class="product-image"
                    >
                <?php endif; ?>

                <div class="product-content">
                    <h3 class="product-name">
                        <?= htmlspecialchars($product->nome) ?>
                    </h3>

                    <p class="product-description">
                        <?= htmlspecialchars($product->descricao) ?>
                    </p>

                    <strong class="product-price">
                        R$ <?= number_format($product->preco, 2, ',', '.') ?>
                    </strong>
                </div>

            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhum produto encontrado.</p>
    <?php endif; ?>
</div>

</body>
</html>
