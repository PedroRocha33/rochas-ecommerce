<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Produtos</title>
</head>
<body>

<h1>Produtos</h1>

<a href="/rochas/app/products/novo">+ Novo Produto</a>

<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Preço</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($products as $p): ?>
        <tr>
            <td><?= $p->id ?></td>
            <td><?= htmlspecialchars($p->nome) ?></td>
            <td>R$ <?= number_format($p->preco, 2, ',', '.') ?></td>
            <td>
                <a href="/rochas/app/products/editar/<?= $p->id ?>">Editar</a>
                |
                <a href="/rochas/app/products/deletar/<?= $p->id ?>"
                   onclick="return confirm('Deseja excluir?')">
                    Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
