<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Painel Administrativo' ?></title>
    <link rel="stylesheet" href="/rochas/assets/css/app/app.css">
</head>
<body>

<div class="app-container">

    <aside class="sidebar">
        <h2 class="logo">ROCHAS</h2>

        <nav>
            <a href="/rochas/app">Dashboard</a>
            <a href="/rochas/app/products">Produtos</a>
            <a href="#">Categorias</a>
            <a href="#">Pedidos</a>
            <a href="#">Usuários</a>
        </nav>
    </aside>

    <main class="main-content">

        <header class="topbar">
            <span>Bem-vindo, Admin</span>
            <a href="/rochas/logout">Sair</a>
        </header>

        <section class="content">
            <?php require $view; ?>
        </section>

    </main>
</div>

</body>
</html>
