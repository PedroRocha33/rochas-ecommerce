/* ===========================
   ESTADO GLOBAL
=========================== */
let cart = [];
let productsStock = {}; // Armazena o estoque de cada produto

/* ===========================
   INICIALIZAR ESTOQUE DOS PRODUTOS
=========================== */
function initializeStock() {
  const productCards = document.querySelectorAll('.product-card');
  
  productCards.forEach(card => {
    const button = card.querySelector('.add-to-cart-btn');
    if (button) {
      const id = parseInt(button.getAttribute('data-id'));
      const estoque = parseInt(button.getAttribute('data-estoque')) || 0;
      
      productsStock[id] = {
        total: estoque,
        disponivel: estoque
      };
      
      // Se não tem estoque, desabilita o botão
      if (estoque <= 0) {
        button.disabled = true;
        button.textContent = 'Indisponível';
        button.style.opacity = '0.5';
        button.style.cursor = 'not-allowed';
      }
    }
  });
  
  console.log('📦 Estoque inicializado:', productsStock);
}

/* ===========================
   VERIFICAR ESTOQUE DISPONÍVEL
=========================== */
function getAvailableStock(productId) {
  const stock = productsStock[productId];
  if (!stock) return 0;
  
  // Quantidade já no carrinho
  const cartItem = cart.find(item => item.id === productId);
  const quantityInCart = cartItem ? cartItem.quantity : 0;
  
  // Estoque disponível = estoque total - quantidade no carrinho
  return stock.total - quantityInCart;
}

/* ===========================
   FILTRO DE PRODUTOS
=========================== */
function filterProducts(category) {
  console.log('🔍 Filtrando por categoria:', category);
  
  // Remove a classe 'active' de todos os botões
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.classList.remove('active');
  });

  // Adiciona 'active' no botão clicado
  const clickedBtn = document.querySelector(`.filter-btn[data-category="${category}"]`);
  if (clickedBtn) {
    clickedBtn.classList.add('active');
  }

  // Pega todos os cards de produtos
  const productCards = document.querySelectorAll('.product-card');
  let visibleCount = 0;

  productCards.forEach(card => {
    const cardCategory = card.getAttribute('data-category');

    if (category === 'todos') {
      card.style.display = '';
      visibleCount++;
    } else {
      if (cardCategory === category) {
        card.style.display = '';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    }
  });

  console.log('✅ Produtos visíveis:', visibleCount);

  // Scroll suave
  const productsGrid = document.getElementById('products-grid');
  if (productsGrid && visibleCount > 0) {
    setTimeout(() => {
      productsGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
  }
}

/* ===========================
   PREÇO
=========================== */
function formatPrice(value) {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value);
}

/* ===========================
   CARRINHO - LOAD & SAVE
=========================== */
function loadCart() {
  const saved = localStorage.getItem('redemac-cart');
  if (saved) {
    try {
      cart = JSON.parse(saved);
      console.log('🛒 Carrinho carregado:', cart.length, 'item(ns)');
    } catch (e) {
      console.error('❌ Erro ao carregar carrinho:', e);
      cart = [];
    }
  }
}

function saveCart() {
  try {
    localStorage.setItem('redemac-cart', JSON.stringify(cart));
    console.log('💾 Carrinho salvo:', cart.length, 'item(ns)');
  } catch (e) {
    console.error('❌ Erro ao salvar carrinho:', e);
  }
}

/* ===========================
   ADICIONAR AO CARRINHO (COM CONTROLE DE ESTOQUE)
=========================== */
function addToCart(button) {
  const id = parseInt(button.getAttribute('data-id'));
  const nome = button.getAttribute('data-nome');
  const preco = parseFloat(button.getAttribute('data-preco'));
  const imagem = button.getAttribute('data-imagem');
  const estoque = parseInt(button.getAttribute('data-estoque')) || 0;

  console.log('➕ Tentando adicionar:', { id, nome, estoque });

  // Validações
  if (!id || !nome || !preco) {
    console.error('❌ Dados incompletos do produto');
    showNotification('Erro ao adicionar produto', 'error');
    return;
  }

  // ✅ VERIFICAR ESTOQUE DISPONÍVEL
  const disponivel = getAvailableStock(id);
  
  if (disponivel <= 0) {
    console.warn('⚠️ Sem estoque disponível para:', nome);
    showNotification('Produto sem estoque!', 'warning');
    
    // Desabilitar botão
    button.disabled = true;
    button.textContent = 'Sem Estoque';
    button.style.opacity = '0.5';
    return;
  }

  // Verifica se o item já está no carrinho
  const existingItem = cart.find(item => item.id === id);

  if (existingItem) {
    existingItem.quantity++;
    console.log('📈 Quantidade aumentada:', existingItem);
  } else {
    cart.push({
      id: id,
      nome: nome,
      preco: preco,
      imagem: imagem || '',
      estoque: estoque,
      quantity: 1
    });
    console.log('✅ Novo item adicionado');
  }

  saveCart();
  updateCartBadge();
  updateStockButtons(); // ✅ Atualiza botões de estoque
  
  // Feedback visual
  const originalText = button.textContent;
  button.textContent = '✓';
  button.style.backgroundColor = '#22c55e';
  
  showNotification(`${nome} adicionado ao carrinho!`, 'success');
  
  setTimeout(() => {
    button.textContent = originalText;
    button.style.backgroundColor = '';
  }, 1000);
}

/* ===========================
   ATUALIZAR BOTÕES DE ESTOQUE
=========================== */
function updateStockButtons() {
  const productCards = document.querySelectorAll('.product-card');
  
  productCards.forEach(card => {
    const button = card.querySelector('.add-to-cart-btn');
    if (!button) return;
    
    const id = parseInt(button.getAttribute('data-id'));
    const disponivel = getAvailableStock(id);
    
    if (disponivel <= 0) {
      button.disabled = true;
      button.textContent = 'Sem Estoque';
      button.style.opacity = '0.5';
      button.style.cursor = 'not-allowed';
    } else {
      button.disabled = false;
      button.textContent = '+';
      button.style.opacity = '1';
      button.style.cursor = 'pointer';
    }
  });
}

/* ===========================
   NOTIFICAÇÕES
=========================== */
function showNotification(message, type = 'info') {
  // Remove notificação anterior se existir
  const oldNotif = document.querySelector('.cart-notification');
  if (oldNotif) oldNotif.remove();
  
  const notification = document.createElement('div');
  notification.className = `cart-notification ${type}`;
  notification.textContent = message;
  
  document.body.appendChild(notification);
  
  setTimeout(() => notification.classList.add('show'), 10);
  
  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => notification.remove(), 300);
  }, 3000);
}

/* ===========================
   BADGE DO CARRINHO
=========================== */
function updateCartBadge() {
  const badge = document.getElementById('cart-badge');
  if (!badge) return;
  
  const total = cart.reduce((sum, item) => sum + item.quantity, 0);
  
  badge.textContent = total;
  badge.style.display = total > 0 ? 'flex' : 'none';
  
  console.log('🔢 Badge atualizado:', total);
}

/* ===========================
   MODAL DO CARRINHO
=========================== */
function toggleCart() {
  const modal = document.getElementById('cart-modal');
  if (!modal) {
    console.error('❌ Modal do carrinho não encontrado');
    return;
  }
  
  const isOpen = modal.classList.contains('show');
  
  if (isOpen) {
    modal.classList.remove('show');
    console.log('🚪 Carrinho fechado');
  } else {
    modal.classList.add('show');
    renderCartItems();
    console.log('🚪 Carrinho aberto');
  }
}

/* ===========================
   RENDERIZAR ITENS DO CARRINHO
=========================== */
function renderCartItems() {
  const container = document.getElementById('cart-content');
  const totalEl = document.getElementById('total-price');
  const footer = document.getElementById('cart-footer');

  if (!container || !totalEl || !footer) {
    console.error('❌ Elementos do carrinho não encontrados');
    return;
  }

  if (cart.length === 0) {
    container.innerHTML = `
      <div class="empty-cart">
        <svg class="empty-cart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="9" cy="21" r="1"></circle>
          <circle cx="20" cy="21" r="1"></circle>
          <path d="m1 1 4 4 2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
        <p>Seu carrinho está vazio</p>
      </div>
    `;
    footer.style.display = 'none';
    totalEl.textContent = formatPrice(0);
    return;
  }

  footer.style.display = 'block';

  container.innerHTML = cart.map(item => {
    const imageUrl = item.imagem 
      ? `/rochas/storage/images/${item.imagem}` 
      : '/rochas/assets/img/placeholder.png';

    const disponivel = getAvailableStock(item.id);
    const maxQuantity = item.quantity + disponivel;
    
    return `
      <div class="cart-item">
        <img src="${imageUrl}" class="cart-item-image" alt="${item.nome}">

        <div class="cart-item-details">
          <div class="cart-item-name">${item.nome}</div>
          <div class="cart-item-price">${formatPrice(item.preco)}</div>
          
          ${item.estoque ? `<div class="cart-item-stock">Estoque: ${maxQuantity}</div>` : ''}

          <div class="cart-item-controls">
            <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">−</button>
            <span class="quantity">${item.quantity}</span>
            <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)" 
                    ${disponivel <= 0 ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''}>+</button>
            <button class="quantity-btn remove" onclick="removeFromCart(${item.id})">🗑</button>
          </div>
        </div>

        <div class="cart-item-total">
          ${formatPrice(item.preco * item.quantity)}
        </div>
      </div>
    `;
  }).join('');

  totalEl.textContent = formatPrice(getTotalPrice());
  console.log('🛒 Carrinho renderizado:', cart.length, 'item(ns)');
}

/* ===========================
   ATUALIZAR QUANTIDADE (COM CONTROLE DE ESTOQUE)
=========================== */
function updateQuantity(id, change) {
  const item = cart.find(i => i.id === id);
  if (!item) {
    console.error('❌ Item não encontrado no carrinho:', id);
    return;
  }

  // Se está aumentando, verificar estoque
  if (change > 0) {
    const disponivel = getAvailableStock(id);
    if (disponivel <= 0) {
      showNotification('Estoque insuficiente!', 'warning');
      return;
    }
  }

  item.quantity += change;
  console.log('🔄 Quantidade atualizada:', item);

  if (item.quantity <= 0) {
    cart = cart.filter(i => i.id !== id);
    console.log('🗑️ Item removido (quantidade zero)');
  }

  saveCart();
  updateCartBadge();
  updateStockButtons(); // ✅ Atualiza botões de estoque
  renderCartItems();
}

/* ===========================
   REMOVER DO CARRINHO
=========================== */
function removeFromCart(id) {
  const itemName = cart.find(i => i.id === id)?.nome;
  
  cart = cart.filter(i => i.id !== id);
  
  console.log('🗑️ Item removido:', itemName);
  
  saveCart();
  updateCartBadge();
  updateStockButtons(); // ✅ Atualiza botões de estoque
  renderCartItems();
}

/* ===========================
   TOTAL DO CARRINHO
=========================== */
function getTotalPrice() {
  return cart.reduce((sum, item) => sum + (item.preco * item.quantity), 0);
}

/* ===========================
   FECHAR MODAL CLICANDO FORA
=========================== */
function setupModalClickOutside() {
  const modal = document.getElementById('cart-modal');
  if (!modal) return;

  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      toggleCart();
    }
  });
}

/* ===========================
   ADICIONAR ESTILOS DAS NOTIFICAÇÕES
=========================== */
function addNotificationStyles() {
  if (document.getElementById('notification-styles')) return;
  
  const style = document.createElement('style');
  style.id = 'notification-styles';
  style.textContent = `
    .cart-notification {
      position: fixed;
      top: 80px;
      right: 20px;
      padding: 15px 20px;
      border-radius: 8px;
      font-weight: 500;
      font-size: 14px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      opacity: 0;
      transform: translateX(400px);
      transition: all 0.3s ease;
      z-index: 9999;
      max-width: 300px;
    }
    
    .cart-notification.show {
      opacity: 1;
      transform: translateX(0);
    }
    
    .cart-notification.success {
      background: #22c55e;
      color: white;
    }
    
    .cart-notification.warning {
      background: #f59e0b;
      color: white;
    }
    
    .cart-notification.error {
      background: #ef4444;
      color: white;
    }
    
    .cart-item-stock {
      font-size: 11px;
      color: #6b7280;
      margin-top: 4px;
    }
  `;
  document.head.appendChild(style);
}

/* ===========================
   INICIALIZAÇÃO
=========================== */
document.addEventListener('DOMContentLoaded', () => {
  console.log('🚀 E-commerce com controle de estoque inicializado!');
  
  addNotificationStyles();
  loadCart();
  initializeStock(); // ✅ Inicializa controle de estoque
  updateCartBadge();
  updateStockButtons(); // ✅ Atualiza botões com base no carrinho
  setupModalClickOutside();
  
  const productCards = document.querySelectorAll('.product-card');
  console.log('📦 Produtos carregados:', productCards.length);
  
  const categoryButtons = document.querySelectorAll('.filter-btn');
  console.log('🏷️ Categorias disponíveis:', categoryButtons.length);
  
  console.log('✅ Sistema pronto!');
  console.log('🛒 Itens no carrinho:', cart.length);
  console.log('📊 Estoque:', productsStock);
});