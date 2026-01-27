<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redemac - Materiais de Construção</title>
    <link rel="icon" href="assets/images/redemac-icon.jpg">
    <link rel="stylesheet" href="/rochas/assets/css/_theme.css">
    <link rel="stylesheet" href="/rochas/assets/css/cart.css">
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo-container">
                <img src="/rochas/assets/img/logo-redemac.png" alt="Redemac Logo" class="logo">
            </div>
            <!-- Navigation Menu -->
            <nav class="nav-menu">
                <div class="nav-item">
                    <a href="index.html" class="nav-link active" onclick="setActiveNav(this, 'loja')">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9,22 9,12 15,12 15,22"></polyline>
                        </svg>
                        Início
                    </a>
                </div>
                <!-- <div class="nav-item">
                    <a href="index.php" class="nav-link" onclick="setActiveNav(this, 'produtos')">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                            <path d="M3 6h18"></path>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        Produtos
                    </a>
                </div> -->
                <div class="nav-item">
                    <a href="sobre.html" class="nav-link" onclick="setActiveNav(this, 'servicos')">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                        </svg>
                        Sobre
                    </a>
                </div>
                <!-- <div class="nav-item">
                    <a href="calculadoras.html" class="nav-link" onclick="setActiveNav(this, 'sobre')">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 16v-4"></path>
                            <path d="M12 8h.01"></path>
                        </svg>
                        Ferramentas
                    </a>
                </div> -->
                <div class="nav-item">
                    <a href="contato.html" class="nav-link" onclick="setActiveNav(this, 'contato')">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        Contato
                    </a>
                </div>
            </nav>

            
            <button class="cart-button" onclick="toggleCart()">
                <svg class="cart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="m1 1 4 4 2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                Carrinho
                <span class="cart-badge" id="cart-badge">0</span>
            </button>
            
        </div>

         <!-- Hambúrguer Menu Button
            <div class="hamburger-menu" onclick="toggleMobileMenu()">
                <div class="hamburger-line"></div>
                <div class="hamburger-line"></div>
                <div class="hamburger-line"></div>
            </div>
        </div>

        <-- Mobile Menu -->
        <!-- <div class="mobile-menu" id="mobileMenu">
            <div class="mobile-menu-content">
                <div class="mobile-nav-item">
                    <a href="index.html" class="mobile-nav-link active" onclick="setActiveMobileNav(this, 'loja')">
                        <svg class="mobile-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9,22 9,12 15,12 15,22"></polyline>
                        </svg>
                        Início
                    </a>
                </div>
                <div class="mobile-nav-item">
                    <a href="ecommerce.html" class="mobile-nav-link" onclick="setActiveMobileNav(this, 'produtos')">
                        <svg class="mobile-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                            <path d="M3 6h18"></path>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        Produtos
                    </a>
                </div>
                <div class="mobile-nav-item">
                    <a href="sobre.html" class="mobile-nav-link" onclick="setActiveMobileNav(this, 'servicos')">
                        <svg class="mobile-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                        </svg>
                        Sobre
                    </a>
                </div>

                <div class="mobile-nav-item">
                    <a href="calculadoras.html" class="mobile-nav-link" onclick="setActiveMobileNav(this, 'sobre')">
                        <svg class="mobile-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 16v-4"></path>
                            <path d="M12 8h.01"></path>
                        </svg>
                        Ferramentas
                    </a>
                </div>
                <div class="mobile-nav-item">
                    <a href="contato.html" class="mobile-nav-link" onclick="setActiveMobileNav(this, 'contato')">
                        <svg class="mobile-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        Contato
                    </a>
                </div>
            </div>
            <div class="mobile-menu-overlay" onclick="toggleMobileMenu()"></div>
        </div>
        </div>
         --> 


    </div>

    <!-- Filtros -->
    <div class="filters-section">
        <div class="filters-content">
            <button class="filter-btn active" onclick="filterProducts('todos')">Todos os Produtos</button>
            <button class="filter-btn" onclick="filterProducts('construcao')">Construção</button>
            <button class="filter-btn" onclick="filterProducts('acabamento')">Acabamento</button>
            <button class="filter-btn" onclick="filterProducts('eletrica')">Elétrica</button>
            <button class="filter-btn" onclick="filterProducts('hidraulica')">Hidráulica</button>
            <button class="filter-btn" onclick="filterProducts('ferramentas')">Ferramentas</button>
        </div>
    </div>

   <!-- Grid de Produtos -->
<div class="main-content">
    <div class="products-grid" id="products-grid">

        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">

                    <img 
                        src="<?= $product->imagem 
                            ? '/rochas/storage/images/' . htmlspecialchars($product->imagem) 
                            : '/rochas/assets/img/placeholder.png' ?>"
                        alt="<?= htmlspecialchars($product->nome) ?>"
                        class="product-image"
                    >

                    <div class="product-content">
                        <span class="product-category">
                            Categoria <?= (int) $product->categoria_id ?>
                        </span>

                        <h3 class="product-name">
                            <?= htmlspecialchars($product->nome) ?>
                        </h3>

                        <p class="product-description">
                            <?= htmlspecialchars($product->descricao) ?>
                        </p>

                        <div class="product-footer">
                            <span class="product-price">
                                R$ <?= number_format($product->preco, 2, ',', '.') ?>
                            </span>

                            <button 
                                class="add-to-cart-btn"
                                data-id="<?= $product->id ?>"
                                data-nome="<?= htmlspecialchars($product->nome) ?>"
                                data-preco="<?= $product->preco ?>"
                            >
                                +
                            </button>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nenhum produto disponível.</p>
        <?php endif; ?>

    </div>
</div>


    <!-- Modal do Carrinho -->
    <div class="cart-modal" id="cart-modal">
        <div class="cart-container">
            <div class="cart-header">
                <h2>Carrinho de Compras</h2>
                <button class="close-btn" onclick="toggleCart()">✕</button>
            </div>
            
            <div class="cart-content" id="cart-content">
                <div class="empty-cart">
                    <svg class="empty-cart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="m1 1 4 4 2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <p>Seu carrinho está vazio</p>
                </div>
            </div>

            <div class="cart-footer" id="cart-footer" style="display: none;">
                <div class="cart-total">
                    <span>Total:</span>
                    <span class="total-price" id="total-price">R$ 0,00</span>
                </div>
                <button class="checkout-btn" onclick="goToCheckout()">Finalizar Compra</button>
            </div>
        </div>
    </div>

    <!-- Página de Checkout -->
    <div class="checkout-page" id="checkout-page" style="display: none;">
        <div class="checkout-header">
            <div class="checkout-header-content">
                <button class="back-btn" onclick="backToStore()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m12 19-7-7 7-7"></path>
                        <path d="M19 12H5"></path>
                    </svg>
                    Voltar à Loja
                </button>
                <div class="checkout-logo">
                    <img src="/assets/images/logo-redemac.png" alt="Redemac Logo">
                    <p>Finalizar Compra</p>
                </div>
                <div class="spacer"></div>
            </div>
        </div>

        <div class="checkout-content">
            <div class="customer-form">
                <h2>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Dados Pessoais
                </h2>
                
                <form id="customer-form">
                    <div class="form-group">
                        <label for="nome">Nome Completo *</label>
                        <input type="text" id="nome" placeholder="Seu nome completo" required>
                    </div>

                    <div class="form-group">
                        <label for="telefone">Telefone *</label>
                        <input type="tel" id="telefone" placeholder="(11) 99999-9999" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" placeholder="seu@email.com">
                    </div>

                    <h3>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        Endereço de Entrega
                    </h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="cep">CEP *</label>
                            <input type="text" id="cep" placeholder="00000-000" required>
                        </div>
                        <div class="form-group">
                            <label for="numero">Número *</label>
                            <input type="text" id="numero" placeholder="123" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="rua">Rua *</label>
                        <input type="text" id="rua" placeholder="Nome da rua" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="bairro">Bairro *</label>
                            <input type="text" id="bairro" placeholder="Nome do bairro" required>
                        </div>
                        <div class="form-group">
                            <label for="cidade">Cidade *</label>
                            <input type="text" id="cidade" placeholder="Nome da cidade" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado *</label>
                        <input type="text" id="estado" placeholder="Estado" required>
                    </div>
                </form>
            </div>

            <div class="order-summary">
                <h2>Resumo do Pedido</h2>
                <div class="summary-items" id="summary-items">
                    <!-- Itens do pedido serão inseridos aqui -->
                </div>
                <div class="summary-total">
                    <span>Total:</span>
                    <span class="summary-price" id="summary-price">R$ 0,00</span>
                </div>
                <button class="finalize-btn" onclick="finalizePurchase()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20,6 9,17 4,12"></polyline>
                    </svg>
                    Finalizar via WhatsApp
                </button>
            </div>
        </div>
    </div>

    <script src="/rochas/assets/js/ecommerce.js"></script>
    
</body>
</html>
