<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /rochas/login.php');
    exit;
}

// Verificar se é admin/gerente
if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] < 2) {
    die('Acesso negado. Você precisa ser administrador ou gerente.');
}

// Garantir que o nome do usuário existe
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$nivel_texto = ($_SESSION['nivel'] ?? 1) == 3 ? 'Administrador' : 'Gerente';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administração</title>
    <link rel="stylesheet" href="/rochas/assets/css/app/app.css">
</head>
<body>

<div class="dashboard">

    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>🏪 Rochas</h2>
            <p class="user-info"><?= htmlspecialchars($usuario_nome) ?></p>
        </div>
        
        <nav class="sidebar-nav">
            <button class="nav-btn active" onclick="showSection('products')">
                📦 Produtos
            </button>
            <button class="nav-btn" onclick="showSection('orders')">
                🛒 Pedidos
            </button>
            <button class="nav-btn logout-btn" onclick="logout()">
                🚪 Sair
            </button>
        </nav>
    </aside>

    <main class="main">

        <div class="header">
            <div>
                <h1>Dashboard Administrativo</h1>
                <span class="badge badge-admin">
                    <?= $nivel_texto ?>
                </span>
            </div>
        </div>

        <!-- ================= PRODUTOS ================= -->
        <section id="products" class="section">

            <div class="section-header">
                <h2>Gerenciar Produtos</h2>
                <button class="btn-primary" onclick="openProductModal()">
                    ➕ Novo Produto
                </button>
            </div>

            <div class="filters">
                <input type="text" id="search-product" placeholder="🔍 Buscar produto..." onkeyup="filterProducts()">
                <select id="filter-category" onchange="filterProducts()">
                    <option value="">Todas Categorias</option>
                </select>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="products-table">
                        <tr>
                            <td colspan="7" class="loading">Carregando produtos...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </section>

        <!-- ================= PEDIDOS ================= -->
        <section id="orders" class="section hidden">

            <div class="section-header">
                <h2>Gerenciar Pedidos</h2>
                <button class="btn-secondary" onclick="loadOrders()">
                    🔄 Atualizar
                </button>
            </div>

            <div class="filters">
                <select id="filter-status" onchange="filterOrders()">
                    <option value="">Todos Status</option>
                    <option value="pendente">Pendente</option>
                    <option value="processando">Processando</option>
                    <option value="enviado">Enviado</option>
                    <option value="entregue">Entregue</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Itens</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="orders-table">
                        <tr>
                            <td colspan="7" class="loading">Carregando pedidos...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </section>

    </main>

</div>

<!-- ================= MODAL PRODUTO - COM UPLOAD DE IMAGEM ================= -->
<div id="product-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title">Novo Produto</h2>
            <button type="button" class="close-btn" onclick="closeProductModal()">✕</button>
        </div>
        
        <form id="product-form" onsubmit="saveProduct(event)" enctype="multipart/form-data">
            <input type="hidden" id="product-id">
            
            <!-- Nome -->
            <div class="form-row">
                <div class="form-group">
                    <label for="product-name">Nome *</label>
                    <input 
                        type="text" 
                        id="product-name" 
                        placeholder="Digite o nome do produto" 
                        required
                    >
                </div>
            </div>
            
            <!-- Descrição -->
            <div class="form-row">
                <div class="form-group">
                    <label for="product-description">Descrição</label>
                    <textarea 
                        id="product-description" 
                        placeholder="Descrição detalhada"
                        rows="3"
                    ></textarea>
                </div>
            </div>
            
            <!-- Preço e Estoque -->
            <div class="form-row">
                <div class="form-group">
                    <label for="product-price">Preço (R$) *</label>
                    <input 
                        type="number" 
                        id="product-price" 
                        placeholder="0.00" 
                        step="0.01"
                        min="0"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="product-stock">Estoque</label>
                    <input 
                        type="number" 
                        id="product-stock" 
                        placeholder="0"
                        min="0"
                        value="0"
                    >
                </div>
            </div>
            
            <!-- Categoria -->
            <div class="form-row">
                <div class="form-group">
                    <label for="product-category">Categoria</label>
                    <select id="product-category">
                        <option value="">Selecione...</option>
                        <!-- Categorias serão carregadas via JS -->
                    </select>
                </div>
            </div>
            
            <!-- Upload de Imagem -->
            <div class="form-row">
                <div class="form-group">
                    <label for="product-image">Imagem do Produto</label>
                    <input 
                        type="file" 
                        id="product-image" 
                        accept="image/jpeg,image/jpg,image/png,image/webp,image/gif"
                        onchange="previewImage(event)"
                    >
                    <small>Formatos aceitos: JPG, PNG, WEBP, GIF (máx. 5MB)</small>
                    
                    <!-- Preview da Imagem -->
                    <div id="image-preview" class="image-preview" style="display: none;">
                        <img id="preview-img" src="" alt="Preview">
                        <button type="button" class="remove-preview" onclick="removeImagePreview()">
                            ✕ Remover
                        </button>
                    </div>
                    
                    <!-- Imagem atual (ao editar) -->
                    <div id="current-image" class="current-image" style="display: none;">
                        <p>Imagem atual:</p>
                        <img id="current-img" src="" alt="Imagem atual">
                        <input type="hidden" id="current-image-path">
                    </div>
                </div>
            </div>
            
            <!-- Seção de Dimensões e Peso -->
            <div class="form-section">
                <h3>📦 Dimensões e Peso (Opcional)</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="product-weight">Peso (kg)</label>
                        <input 
                            type="number" 
                            id="product-weight" 
                            placeholder="0.00"
                            step="0.01"
                            min="0"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="product-width">Largura (cm)</label>
                        <input 
                            type="number" 
                            id="product-width" 
                            placeholder="0"
                            min="0"
                        >
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="product-height">Altura (cm)</label>
                        <input 
                            type="number" 
                            id="product-height" 
                            placeholder="0"
                            min="0"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="product-length">Comprimento (cm)</label>
                        <input 
                            type="number" 
                            id="product-length" 
                            placeholder="0"
                            min="0"
                        >
                    </div>
                </div>
            </div>
            
            <!-- Produto Ativo -->
            <div class="form-row">
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="product-active" checked>
                        Produto ativo
                    </label>
                </div>
            </div>
            
            <!-- Botões -->
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    💾 Salvar
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeProductModal()">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL PEDIDO ================= -->
<div class="modal hidden" id="order-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Atualizar Status do Pedido</h3>
            <button class="close-btn" onclick="closeOrderModal()">✕</button>
        </div>

        <input type="hidden" id="order-id">

        <div class="form-group">
            <label for="order-status">Novo Status</label>
            <select id="order-status">
                <option value="pendente">Pendente</option>
                <option value="processando">Processando</option>
                <option value="enviado">Enviado</option>
                <option value="entregue">Entregue</option>
                <option value="cancelado">Cancelado</option>
            </select>
        </div>

        <div class="modal-actions">
            <button class="btn-primary" onclick="updateOrderStatus()">Atualizar</button>
            <button class="btn-secondary" onclick="closeOrderModal()">Cancelar</button>
        </div>
    </div>
</div>

<script src="/rochas/assets/js/app.js"></script>
</body>
</html>