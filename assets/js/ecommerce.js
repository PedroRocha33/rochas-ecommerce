/* ===========================
   ESTADO GLOBAL
=========================== */
let products = [];
let filteredProducts = [];
let cart = [];

/* ===========================
   API → CARREGAR PRODUTOS
=========================== */
async function loadProducts() {
  try {
    const response = await fetch('/rochas/api/products');
    const result = await response.json();

    if (result.status !== 'success') {
      console.error('Erro ao carregar produtos');
      return;
    }

    products = result.data;
    filteredProducts = products;
    renderProducts();
  } catch (error) {
    console.error('Erro na API:', error);
  }
}

/* ===========================
   RENDERIZAR PRODUTOS
=========================== */
function renderProducts() {
  const grid = document.getElementById('products-grid');
  grid.innerHTML = '';

  if (!filteredProducts.length) {
    grid.innerHTML = '<p>Nenhum produto encontrado.</p>';
    return;
  }

  filteredProducts.forEach(product => {
    const imageUrl = product.imagem
      ? `/rochas/storage/images/${product.imagem}`
      : '/rochas/assets/img/placeholder.png';

    grid.innerHTML += `
      <div class="product-card">
        <img src="${imageUrl}" class="product-image">

        <div class="product-content">
          <span class="product-category">
            Categoria ${product.categoria_id}
          </span>

          <h3 class="product-name">${product.nome}</h3>

          <p class="product-description">
            ${product.descricao ?? ''}
          </p>

          <div class="product-footer">
            <span class="product-price">
              ${formatPrice(product.preco)}
            </span>

            <button class="add-to-cart-btn" onclick="addToCart(${product.id})">
              +
            </button>
          </div>
        </div>
      </div>
    `;
  });
}

/* ===========================
   FILTRO (SEM BUG)
=========================== */
function filterProducts(category, el) {
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  el.classList.add('active');

  if (category === 'todos') {
    filteredProducts = products;
  } else {
    filteredProducts = products.filter(
      p => String(p.categoria_id) === String(category)
    );
  }

  renderProducts();
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
   CARRINHO
=========================== */
function loadCart() {
  const saved = localStorage.getItem('redemac-cart');
  if (saved) cart = JSON.parse(saved);
}

function saveCart() {
  localStorage.setItem('redemac-cart', JSON.stringify(cart));
}

function addToCart(id) {
  const product = products.find(p => p.id === id);
  if (!product) return;

  const item = cart.find(i => i.id === id);
  if (item) {
    item.quantity++;
  } else {
    cart.push({
      id: product.id,
      nome: product.nome,
      preco: product.preco,
      imagem: product.imagem,
      quantity: 1
    });
  }

  saveCart();
  updateCartBadge();
}

/* ===========================
   BADGE
=========================== */
function updateCartBadge() {
  const badge = document.getElementById('cart-badge');
  const total = cart.reduce((s, i) => s + i.quantity, 0);
  badge.textContent = total;
  badge.style.display = total ? 'flex' : 'none';
}

/* ===========================
   MODAL
=========================== */
function toggleCart() {
  const modal = document.getElementById('cart-modal');
  modal.classList.toggle('show');
  renderCartItems();
}

/* ===========================
   ITENS DO CARRINHO
=========================== */
function renderCartItems() {
  const container = document.getElementById('cart-content');
  const totalEl = document.getElementById('total-price');
  const footer = document.getElementById('cart-footer');

  if (!cart.length) {
    container.innerHTML = '<p>Seu carrinho está vazio</p>';
    footer.style.display = 'none';
    totalEl.textContent = formatPrice(0);
    return;
  }

  footer.style.display = 'block';

  container.innerHTML = cart.map(item => {
    const image = item.imagem
      ? `/rochas/storage/images/${item.imagem}`
      : '/rochas/assets/img/placeholder.png';

    return `
      <div class="cart-item">
        <img src="${image}" class="cart-item-image">

        <div class="cart-item-details">
          <div class="cart-item-name">${item.nome}</div>
          <div class="cart-item-price">${formatPrice(item.preco)}</div>

          <div class="cart-item-controls">
            <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">−</button>
            <span class="quantity">${item.quantity}</span>
            <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
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
}

function updateQuantity(id, change) {
  const item = cart.find(i => i.id === id);
  if (!item) return;

  item.quantity += change;
  if (item.quantity <= 0) {
    cart = cart.filter(i => i.id !== id);
  }

  saveCart();
  updateCartBadge();
  renderCartItems();
}

function removeFromCart(id) {
  cart = cart.filter(i => i.id !== id);
  saveCart();
  updateCartBadge();
  renderCartItems();
}

function getTotalPrice() {
  return cart.reduce((s, i) => s + i.preco * i.quantity, 0);
}

/* ===========================
   INIT
=========================== */
document.addEventListener('DOMContentLoaded', () => {
  loadCart();
  loadProducts();
  updateCartBadge();
});
