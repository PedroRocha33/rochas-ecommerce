// ===============================
// ESTADO GLOBAL
// ===============================
let allProducts = [];
let allOrders = [];
let allCategories = [];
let editingProductId = null;

// ===============================
// INICIALIZAÇÃO
// ===============================
document.addEventListener('DOMContentLoaded', () => {
    console.log('App inicializado');
    
    // Garantir que modais estejam escondidos
    document.querySelectorAll('.modal').forEach(modal => {
        modal.classList.add('hidden');
        modal.style.display = 'none';
    });
    
    // Carregar dados
    loadCategories();
    loadProducts();
    loadOrders();
});

// ===============================
// CONTROLE DE SEÇÕES
// ===============================
function showSection(section) {
    document.querySelectorAll('.section').forEach(s => s.classList.add('hidden'));
    document.getElementById(section).classList.remove('hidden');
    
    document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('active'));
    event?.target?.classList.add('active');
    
    if (section === 'orders') {
        loadOrders();
    }
}

// ===============================
// CATEGORIAS
// ===============================
async function loadCategories() {
    try {
        const response = await fetch('/rochas/api/categories.php');
        const data = await response.json();
        
        if (response.ok) {
            allCategories = data;
            
            // Preencher select do filtro
            const filterSelect = document.getElementById('filter-category');
            if (filterSelect) {
                filterSelect.innerHTML = '<option value="">Todas Categorias</option>';
                data.forEach(cat => {
                    filterSelect.innerHTML += `<option value="${cat.id}">${cat.nome}</option>`;
                });
            }
            
            // Preencher select do modal
            const modalSelect = document.getElementById('product-category');
            if (modalSelect) {
                modalSelect.innerHTML = '<option value="">Selecione...</option>';
                data.forEach(cat => {
                    modalSelect.innerHTML += `<option value="${cat.id}">${cat.nome}</option>`;
                });
            }
        }
    } catch (error) {
        console.error('Erro ao carregar categorias:', error);
    }
}

// ===============================
// PREVIEW DE IMAGEM
// ===============================
function previewImage(event) {
    console.log('previewImage chamada');
    const file = event.target.files[0];
    
    if (!file) {
        console.log('Nenhum arquivo selecionado');
        return;
    }
    
    console.log('Arquivo:', file.name, 'Tamanho:', file.size, 'Tipo:', file.type);
    
    // Validar tamanho (máx 5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('❌ Arquivo muito grande! Tamanho máximo: 5MB');
        event.target.value = '';
        return;
    }
    
    // Validar tipo
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    if (!allowedTypes.includes(file.type)) {
        alert('❌ Formato inválido! Use: JPG, PNG, WEBP ou GIF');
        event.target.value = '';
        return;
    }
    
    // Mostrar preview
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('image-preview');
        const img = document.getElementById('preview-img');
        
        if (preview && img) {
            img.src = e.target.result;
            preview.style.display = 'block';
            console.log('Preview exibido');
        }
        
        // Esconder imagem atual se estiver editando
        const currentImage = document.getElementById('current-image');
        if (currentImage) {
            currentImage.style.display = 'none';
        }
    };
    reader.readAsDataURL(file);
}

function removeImagePreview() {
    const imageInput = document.getElementById('product-image');
    const preview = document.getElementById('image-preview');
    
    if (imageInput) imageInput.value = '';
    if (preview) preview.style.display = 'none';
    
    // Mostrar imagem atual novamente se estiver editando
    const currentImage = document.getElementById('current-image');
    const currentImagePath = document.getElementById('current-image-path');
    if (currentImage && currentImagePath && currentImagePath.value) {
        currentImage.style.display = 'block';
    }
}

// ===============================
// PRODUTOS - LISTAR
// ===============================
async function loadProducts() {
    const tbody = document.getElementById('products-table');
    tbody.innerHTML = '<tr><td colspan="7" class="loading">Carregando...</td></tr>';
    
    try {
        const response = await fetch('/rochas/api/products.php');
        const data = await response.json();
        
        console.log('Response status:', response.status);
        console.log('Response data:', data);
        
        if (response.ok) {
            allProducts = data;
            renderProducts(data);
        } else {
            tbody.innerHTML = `<tr><td colspan="7" class="empty">❌ ${data.error}</td></tr>`;
            
            // Se não autorizado, redirecionar para login
            if (response.status === 401) {
                alert('Sessão expirada. Redirecionando para login...');
                setTimeout(() => {
                    window.location.href = '/rochas/login.php';
                }, 2000);
            }
        }
    } catch (error) {
        console.error('Erro:', error);
        tbody.innerHTML = '<tr><td colspan="7" class="empty">❌ Erro ao conectar com o servidor</td></tr>';
    }
}

function renderProducts(products) {
    const tbody = document.getElementById('products-table');
    
    if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="empty">Nenhum produto encontrado</td></tr>';
        return;
    }
    
    tbody.innerHTML = '';
    products.forEach(p => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${p.id}</td>
            <td><strong>${escapeHtml(p.nome)}</strong></td>
            <td>${p.categoria_nome || '-'}</td>
            <td>R$ ${parseFloat(p.preco).toFixed(2)}</td>
            <td>${p.estoque}</td>
            <td>
                <span class="badge ${p.ativo ? 'badge-success' : 'badge-danger'}">
                    ${p.ativo ? 'Ativo' : 'Inativo'}
                </span>
            </td>
            <td>
                <button class="btn-sm btn-edit" onclick="editProduct(${p.id})">✏️ Editar</button>
                <button class="btn-sm btn-delete" onclick="deleteProduct(${p.id})">🗑️ Excluir</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// ===============================
// PRODUTOS - FILTRAR
// ===============================
function filterProducts() {
    const search = document.getElementById('search-product').value.toLowerCase();
    const categoryId = document.getElementById('filter-category').value;
    
    let filtered = allProducts;
    
    if (search) {
        filtered = filtered.filter(p => 
            p.nome.toLowerCase().includes(search) ||
            p.descricao?.toLowerCase().includes(search)
        );
    }
    
    if (categoryId) {
        filtered = filtered.filter(p => p.categoria_id == categoryId);
    }
    
    renderProducts(filtered);
}

// ===============================
// PRODUTOS - MODAL
// ===============================
function openProductModal() {
    console.log('Abrindo modal de produto');
    editingProductId = null;
    
    const modalTitle = document.getElementById('modal-title');
    const productForm = document.getElementById('product-form');
    const productId = document.getElementById('product-id');
    const productActive = document.getElementById('product-active');
    const imagePreview = document.getElementById('image-preview');
    const currentImage = document.getElementById('current-image');
    const currentImagePath = document.getElementById('current-image-path');
    
    if (modalTitle) modalTitle.textContent = 'Novo Produto';
    if (productForm) productForm.reset();
    if (productId) productId.value = '';
    if (productActive) productActive.checked = true;
    
    // Limpar preview e imagem atual
    if (imagePreview) imagePreview.style.display = 'none';
    if (currentImage) currentImage.style.display = 'none';
    if (currentImagePath) currentImagePath.value = '';
    
    const modal = document.getElementById('product-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
    }
}

function closeProductModal() {
    const modal = document.getElementById('product-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }
    editingProductId = null;
}

// ===============================
// PRODUTOS - SALVAR COM UPLOAD
// ===============================
async function saveProduct(event) {
    event.preventDefault();
    
    console.log('=== SALVANDO PRODUTO ===');
    
    // Usar FormData para suportar upload de arquivo
    const formData = new FormData();
    
    // Adicionar campos básicos
    const nome = document.getElementById('product-name').value.trim();
    const preco = document.getElementById('product-price').value;
    
    console.log('Nome:', nome);
    console.log('Preço:', preco);
    
    if (!nome || !preco) {
        alert('❌ Nome e preço são obrigatórios!');
        return;
    }
    
    formData.append('nome', nome);
    formData.append('descricao', document.getElementById('product-description').value);
    formData.append('preco', preco);
    formData.append('estoque', document.getElementById('product-stock').value || '0');
    
    const categoryId = document.getElementById('product-category').value;
    if (categoryId) {
        formData.append('categoria_id', categoryId);
    }
    
    // Adicionar arquivo de imagem (se selecionado)
    const imageInput = document.getElementById('product-image');
    if (imageInput && imageInput.files && imageInput.files[0]) {
        const imageFile = imageInput.files[0];
        console.log('Arquivo de imagem:', imageFile.name, imageFile.size, 'bytes');
        formData.append('imagem', imageFile);
    } else {
        console.log('Nenhuma imagem selecionada');
        
        // Se estiver editando e não selecionou nova imagem, manter a atual
        if (editingProductId) {
            const currentImagePath = document.getElementById('current-image-path');
            if (currentImagePath && currentImagePath.value) {
                formData.append('imagem_atual', currentImagePath.value);
                console.log('Mantendo imagem atual:', currentImagePath.value);
            }
        }
    }
    
    // Adicionar dimensões e peso
    const weight = document.getElementById('product-weight').value;
    const width = document.getElementById('product-width').value;
    const height = document.getElementById('product-height').value;
    const length = document.getElementById('product-length').value;
    
    if (weight) formData.append('weight', weight);
    if (width) formData.append('width', width);
    if (height) formData.append('height', height);
    if (length) formData.append('length', length);
    
    const ativo = document.getElementById('product-active').checked ? '1' : '0';
    formData.append('ativo', ativo);
    
    // IMPORTANTE: Sempre usar POST (mesmo para edição)
    let url = '/rochas/api/products.php';
    let method = 'POST';
    
    if (editingProductId) {
        formData.append('id', editingProductId);
        formData.append('_method', 'PUT'); // Indicador de edição
        console.log('Editando produto ID:', editingProductId);
    } else {
        console.log('Criando novo produto');
    }
    
    // Log dos dados que serão enviados
    console.log('=== DADOS DO FORMDATA ===');
    for (let pair of formData.entries()) {
        const value = pair[1] instanceof File ? `[File: ${pair[1].name}]` : pair[1];
        console.log(`${pair[0]}: ${value}`);
    }
    
    try {
        console.log('Enviando requisição para:', url);
        const response = await fetch(url, {
            method: method,
            body: formData
        });
        
        console.log('Status da resposta:', response.status);
        const result = await response.json();
        console.log('Resposta do servidor:', result);
        
        if (response.ok) {
            showSuccess(result.message || 'Produto salvo com sucesso!');
            closeProductModal();
            loadProducts();
        } else {
            showError(result.error || 'Erro ao salvar produto');
        }
    } catch (error) {
        console.error('Erro na requisição:', error);
        showError('Erro ao salvar produto: ' + error.message);
    }
}

// ===============================
// PRODUTOS - EDITAR
// ===============================
async function editProduct(id) {
    console.log('Editando produto ID:', id);
    
    try {
        const response = await fetch(`/rochas/api/products.php?id=${id}`);
        const product = await response.json();
        
        console.log('Produto carregado:', product);
        
        if (response.ok) {
            editingProductId = id;
            
            document.getElementById('modal-title').textContent = 'Editar Produto';
            document.getElementById('product-id').value = product.id;
            document.getElementById('product-name').value = product.nome || '';
            document.getElementById('product-description').value = product.descricao || '';
            document.getElementById('product-price').value = product.preco || '';
            document.getElementById('product-stock').value = product.estoque || '0';
            document.getElementById('product-category').value = product.categoria_id || '';
            
            // Campos de dimensões
            document.getElementById('product-weight').value = product.weight || '';
            document.getElementById('product-width').value = product.width || '';
            document.getElementById('product-height').value = product.height || '';
            document.getElementById('product-length').value = product.length || '';
            
            document.getElementById('product-active').checked = product.ativo == 1;
            
            // Mostrar imagem atual
            if (product.imagem) {
                const currentImage = document.getElementById('current-image');
                const currentImg = document.getElementById('current-img');
                const currentImagePath = document.getElementById('current-image-path');
                
                if (currentImg) currentImg.src = `/rochas/storage/images/${product.imagem}`;
                if (currentImagePath) currentImagePath.value = product.imagem;
                if (currentImage) currentImage.style.display = 'block';
            }
            
            // Limpar preview de nova imagem
            const imagePreview = document.getElementById('image-preview');
            const productImage = document.getElementById('product-image');
            
            if (imagePreview) imagePreview.style.display = 'none';
            if (productImage) productImage.value = '';
            
            const modal = document.getElementById('product-modal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        } else {
            showError(product.error || 'Erro ao carregar produto');
        }
    } catch (error) {
        console.error('Erro:', error);
        showError('Erro ao carregar produto: ' + error.message);
    }
}

// ===============================
// PRODUTOS - EXCLUIR
// ===============================
async function deleteProduct(id) {
    if (!confirm('Tem certeza que deseja excluir este produto?')) {
        return;
    }
    
    try {
        const response = await fetch('/rochas/api/products.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            showSuccess(result.message || 'Produto excluído com sucesso!');
            loadProducts();
        } else {
            showError(result.error || 'Erro ao excluir produto');
        }
    } catch (error) {
        console.error('Erro:', error);
        showError('Erro ao excluir produto: ' + error.message);
    }
}

// ===============================
// PEDIDOS - LISTAR
// ===============================
async function loadOrders() {
    const tbody = document.getElementById('orders-table');
    if (!tbody) return;
    
    tbody.innerHTML = '<tr><td colspan="7" class="loading">Carregando...</td></tr>';
    
    try {
        const response = await fetch('/rochas/api/orders.php');
        const data = await response.json();
        
        if (response.ok) {
            allOrders = data;
            renderOrders(data);
        } else {
            showError('Erro ao carregar pedidos: ' + data.error);
        }
    } catch (error) {
        console.error('Erro:', error);
        showError('Erro ao conectar com o servidor');
    }
}

function renderOrders(orders) {
    const tbody = document.getElementById('orders-table');
    if (!tbody) return;
    
    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="empty">Nenhum pedido encontrado</td></tr>';
        return;
    }
    
    tbody.innerHTML = '';
    orders.forEach(o => {
        const tr = document.createElement('tr');
        const statusClass = {
            'pendente': 'badge-warning',
            'processando': 'badge-info',
            'enviado': 'badge-primary',
            'entregue': 'badge-success',
            'cancelado': 'badge-danger'
        }[o.status] || 'badge-secondary';
        
        tr.innerHTML = `
            <td>#${o.id}</td>
            <td>
                <strong>${escapeHtml(o.cliente_nome)}</strong><br>
                <small>${escapeHtml(o.cliente_email)}</small>
            </td>
            <td>R$ ${parseFloat(o.total || 0).toFixed(2)}</td>
            <td>${o.total_itens}</td>
            <td>
                <span class="badge ${statusClass}">
                    ${capitalizeFirst(o.status)}
                </span>
            </td>
            <td>${formatDate(o.criado_em)}</td>
            <td>
                <button class="btn-sm btn-edit" onclick="openOrderModal(${o.id}, '${o.status}')">
                    📝 Status
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// ===============================
// PEDIDOS - FILTRAR
// ===============================
function filterOrders() {
    const status = document.getElementById('filter-status').value;
    
    let filtered = allOrders;
    
    if (status) {
        filtered = filtered.filter(o => o.status === status);
    }
    
    renderOrders(filtered);
}

// ===============================
// PEDIDOS - MODAL STATUS
// ===============================
function openOrderModal(id, currentStatus) {
    document.getElementById('order-id').value = id;
    document.getElementById('order-status').value = currentStatus;
    
    const modal = document.getElementById('order-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
    }
}

function closeOrderModal() {
    const modal = document.getElementById('order-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }
}

async function updateOrderStatus() {
    const id = document.getElementById('order-id').value;
    const status = document.getElementById('order-status').value;
    
    try {
        const response = await fetch('/rochas/api/orders.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, status })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            showSuccess(result.message || 'Status atualizado!');
            closeOrderModal();
            loadOrders();
        } else {
            showError(result.error || 'Erro ao atualizar status');
        }
    } catch (error) {
        console.error('Erro:', error);
        showError('Erro ao atualizar status: ' + error.message);
    }
}

// ===============================
// UTILITÁRIOS
// ===============================
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function capitalizeFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function showSuccess(message) {
    alert('✅ ' + message);
}

function showError(message) {
    alert('❌ ' + message);
}

function logout() {
    if (confirm('Deseja realmente sair?')) {
        window.location.href = '/rochas/logout.php';
    }
}

// Fechar modais ao clicar fora
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.classList.add('hidden');
        e.target.style.display = 'none';
    }
});

// Fechar modais com ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeProductModal();
        closeOrderModal();
    }
});

console.log('✅ app.js carregado com sucesso');