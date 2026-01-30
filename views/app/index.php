<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /rochas/login');
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
    <style>
        /* ==========================================
           MODAL DE PEDIDO - ESTILO MODERNO
           ========================================== */

        /* Modal overlay com blur */
        .modal {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            transition: all 0.3s ease;
        }

        /* Container do modal - mais largo */
        .modal-lg {
            max-width: 900px !important;
            width: 95%;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header do modal com gradiente */
        #order-modal .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px 32px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: none;
        }

        #order-modal .modal-header h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        #order-modal .modal-header .close-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #order-modal .modal-header .close-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        /* Badge de status */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-badge.processing {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-badge.shipped {
            background: #e0e7ff;
            color: #3730a3;
        }

        .status-badge.delivered {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Seções do modal */
        .modal-section {
            padding: 24px 32px;
            border-bottom: 1px solid #e5e7eb;
            transition: background 0.2s;
        }

        .modal-section:hover {
            background: #f9fafb;
        }

        .modal-section:last-of-type {
            border-bottom: none;
        }

        .modal-section h4 {
            margin: 0 0 16px 0;
            color: #1f2937;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Cards de informação */
        .info-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .info-card p {
            margin: 8px 0;
            color: #4b5563;
            line-height: 1.6;
        }

        .info-card strong {
            color: #1f2937;
            font-weight: 600;
            display: inline-block;
            min-width: 100px;
        }

        /* Endereço formatado */
        .address-box {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            font-size: 15px;
            line-height: 1.8;
            color: #4b5563;
        }

        .address-box strong {
            color: #1f2937;
            font-size: 16px;
            display: block;
            margin-bottom: 8px;
        }

        /* Tabela de itens moderna */
        .items-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 12px;
        }

        .items-table thead {
            background: #f3f4f6;
        }

        .items-table thead th {
            padding: 14px 16px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }

        .items-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s;
        }

        .items-table tbody tr:hover {
            background: #f9fafb;
        }

        .items-table tbody td {
            padding: 16px;
            vertical-align: middle;
        }

        /* Imagem do produto */
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 12px;
            border: 2px solid #e5e7eb;
            vertical-align: middle;
        }

        .product-info {
            display: inline-block;
            vertical-align: middle;
        }

        .product-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 15px;
            display: block;
            margin-bottom: 4px;
        }

        .product-sku {
            font-size: 12px;
            color: #9ca3af;
        }

        /* Quantidade badge */
        .qty-badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
        }

        /* Preços */
        .price {
            font-weight: 700;
            color: #1f2937;
            font-size: 16px;
        }

        .price-detail {
            font-size: 12px;
            color: #6b7280;
            display: block;
            margin-top: 4px;
        }

        /* Resumo financeiro */
        .financial-summary {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            padding: 20px;
            border-radius: 12px;
            margin-top: 16px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #d1d5db;
            font-size: 15px;
        }

        .summary-row:last-child {
            border-bottom: none;
            padding-top: 16px;
            margin-top: 8px;
            border-top: 2px solid #9ca3af;
        }

        .summary-label {
            color: #4b5563;
            font-weight: 500;
        }

        .summary-value {
            font-weight: 700;
            color: #1f2937;
        }

        .summary-row.total {
            font-size: 20px;
        }

        .summary-row.total .summary-value {
            color: #667eea;
        }

        /* Select de status customizado */
        .status-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            color: #1f2937;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .status-select:hover {
            border-color: #667eea;
        }

        .status-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Ações do modal */
        #order-modal .modal-actions {
            padding: 24px 32px;
            background: #f9fafb;
            border-radius: 0 0 16px 16px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        #order-modal .modal-actions .btn-primary,
        #order-modal .modal-actions .btn-secondary {
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        #order-modal .modal-actions .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        #order-modal .modal-actions .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        #order-modal .modal-actions .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        #order-modal .modal-actions .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .modal-lg {
                width: 98%;
                margin: 10px;
                max-height: 95vh;
            }
            
            .modal-section {
                padding: 16px 20px;
            }
            
            .items-table {
                font-size: 13px;
            }
            
            .product-img {
                width: 50px;
                height: 50px;
            }
            
            #order-modal .modal-actions {
                flex-direction: column;
            }
            
            #order-modal .modal-actions .btn-primary,
            #order-modal .modal-actions .btn-secondary {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
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

<!-- ================= MODAL PRODUTO ================= -->
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

<!-- ================= MODAL PEDIDO MODERNO ================= -->
<div class="modal hidden" id="order-modal">
    <div class="modal-content modal-lg">

        <!-- HEADER -->
        <div class="modal-header">
            <h3>
                📦 Pedido #<span id="order-id"></span>
                <span id="order-status-badge" class="status-badge"></span>
            </h3>
            <button class="close-btn" onclick="closeOrderModal()" aria-label="Fechar">✖</button>
        </div>

        <!-- INFORMAÇÕES DO CLIENTE -->
        <div class="modal-section">
            <h4>👤 Informações do Cliente</h4>
            <div class="info-card">
                <p><strong>Nome:</strong> <span id="order-client"></span></p>
                <p><strong>Email:</strong> <span id="order-email"></span></p>
                <p><strong>Telefone:</strong> <span id="order-phone">-</span></p>
            </div>
        </div>

        <!-- ENDEREÇO DE ENTREGA -->
        <div class="modal-section">
            <h4>🏠 Endereço de Entrega</h4>
            <div class="address-box" id="order-address"></div>
        </div>

        <!-- ITENS DO PEDIDO -->
        <div class="modal-section">
            <h4>🧾 Itens do Pedido</h4>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th style="text-align: center; width: 100px;">Qtd</th>
                        <th style="text-align: right; width: 150px;">Preço</th>
                    </tr>
                </thead>
                <tbody id="order-items"></tbody>
            </table>

            <!-- Resumo Financeiro -->
            <div class="financial-summary">
                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value" id="order-subtotal">R$ 0,00</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">
                        Frete (<span id="shipping-type">-</span> • <span id="shipping-days">0</span> dias)
                    </span>
                    <span class="summary-value" id="order-shipping">R$ 0,00</span>
                </div>
                <div class="summary-row total">
                    <span class="summary-label">TOTAL</span>
                    <span class="summary-value" id="order-total">R$ 0,00</span>
                </div>
            </div>
        </div>

        <!-- ALTERAR STATUS -->
        <div class="modal-section">
            <h4>📌 Status do Pedido</h4>
            <select id="order-status" class="status-select">
                <option value="pending">⏳ Pendente</option>
                <option value="processing">🔄 Processando</option>
                <option value="shipped">🚚 Enviado</option>
                <option value="delivered">✅ Entregue</option>
                <option value="cancelled">❌ Cancelado</option>
            </select>
        </div>

        <!-- AÇÕES -->
        <div class="modal-actions">
            <button class="btn-secondary" onclick="generateLabel()">
                🏷️ Gerar Etiqueta
            </button>
            <button class="btn-primary" onclick="updateOrderStatus()">
                💾 Salvar Alterações
            </button>
        </div>

    </div>
</div>

<script src="/rochas/assets/js/app.js"></script>
</body>
</html>