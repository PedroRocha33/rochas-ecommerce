// app.js
// ===============================
// SISTEMA DE TOAST
// ===============================
function showToast(message, type = 'info') {
    // Remover toast anterior se existir
    const existingToast = document.querySelector('.toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Criar elemento do toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    // Ícones por tipo
    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };
    
    toast.innerHTML = `
        <span class="toast-icon">${icons[type] || icons.info}</span>
        <span class="toast-message">${message}</span>
        <button class="toast-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    // Adicionar ao body
    document.body.appendChild(toast);
    
    // Animar entrada
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Remover automaticamente após 4 segundos
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ===============================
// ESTADO GLOBAL
// ===============================
let allProducts = [];
let allOrders = [];
let allCategories = [];
let editingProductId = null;
let currentOrderId = null;

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
        showToast('Arquivo muito grande! Tamanho máximo: 5MB', 'error');
        event.target.value = '';
        return;
    }
    
    // Validar tipo
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    if (!allowedTypes.includes(file.type)) {
        showToast('Formato inválido! Use: JPG, PNG, WEBP ou GIF', 'error');
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
                showToast('Sessão expirada. Redirecionando para login...', 'warning');
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
        showToast('Nome e preço são obrigatórios!', 'error');
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
    tbody.innerHTML = '';

    if (!orders.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="empty">Nenhum pedido encontrado</td>
            </tr>
        `;
        return;
    }

    orders.forEach(o => {
        tbody.innerHTML += `
            <tr>
                <td>#${o.pedido_id}</td>

                <td>
                    <strong>${o.cliente_nome}</strong><br>
                    <small>${o.cliente_email}</small>
                </td>

                <td>R$ ${Number(o.total).toFixed(2)}</td>

                <td>${o.total_itens}</td>

                <td>
                    <span class="badge status-${o.status}">
                        ${o.status}
                    </span>
                </td>

                <td>${new Date(o.criado_em).toLocaleDateString('pt-BR')}</td>

                <td>
                    <button class="btn-sm" onclick="viewOrder(${o.pedido_id})">
                        📦 Detalhes
                    </button>
                </td>
            </tr>
        `;
    });
}

// ===============================
// PEDIDOS - VER DETALHES
// ===============================
async function viewOrder(id) {
    console.log('Carregando detalhes do pedido:', id);
    
    try {
        const response = await fetch(`/rochas/api/orders.php?id=${id}`);
        const pedido = await response.json();
        
        console.log('Pedido:', pedido);
        
        if (response.ok) {
            currentOrderId = id;
            
            // Preencher dados do modal
            document.getElementById('order-id').textContent = pedido.id;
            document.getElementById('order-client').textContent = pedido.cliente_nome;
            document.getElementById('order-email').textContent = pedido.cliente_email;
            
            // Endereço
            const endereco = pedido.rua 
                ? `${pedido.rua}, ${pedido.numero} - ${pedido.bairro}<br>${pedido.cidade}/${pedido.estado} - CEP: ${pedido.cep}`
                : 'Endereço não informado';
            document.getElementById('order-address').innerHTML = endereco;
            
            // Itens do pedido
            const itemsTable = document.getElementById('order-items');
            itemsTable.innerHTML = '';
            
            if (pedido.itens && pedido.itens.length > 0) {
                pedido.itens.forEach(item => {
                    itemsTable.innerHTML += `
                        <tr>
                            <td>
                                ${item.produto_imagem ? 
                                    `<img src="/rochas/storage/images/${item.produto_imagem}" 
                                          alt="${escapeHtml(item.produto_nome)}" 
                                          style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">` 
                                    : ''}
                                ${escapeHtml(item.produto_nome)}
                            </td>
                            <td>${item.quantidade}</td>
                            <td>R$ ${parseFloat(item.preco_unitario).toFixed(2)}</td>
                        </tr>
                    `;
                });
            } else {
                itemsTable.innerHTML = '<tr><td colspan="3" class="empty">Nenhum item encontrado</td></tr>';
            }
            
            // Status
            document.getElementById('order-status').value = pedido.status;
            
            // Abrir modal
            const modal = document.getElementById('order-modal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        } else {
            showError(pedido.error || 'Erro ao carregar pedido');
        }
    } catch (error) {
        console.error('Erro:', error);
        showError('Erro ao carregar pedido: ' + error.message);
    }
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
// PEDIDOS - MODAL
// ===============================
function closeOrderModal() {
    const modal = document.getElementById('order-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }
    currentOrderId = null;
}

async function updateOrderStatus() {
    if (!currentOrderId) {
        showError('Nenhum pedido selecionado');
        return;
    }
    
    const status = document.getElementById('order-status').value;
    
    try {
        const response = await fetch('/rochas/api/orders.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: currentOrderId, status })
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
// GERAR ETIQUETA
// ===============================
function generateLabel() {
    if (!currentOrderId) {
        showError('Nenhum pedido selecionado');
        return;
    }
    
    // Abrir em nova janela ou gerar PDF
    window.open(`/rochas/api/label.php?order_id=${currentOrderId}`, '_blank');
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
    showToast(message, 'success');
}

function showError(message) {
    showToast(message, 'error');
}

async function logout() {
    if (!confirm('Deseja realmente sair?')) return;

    try {
        const response = await fetch('/rochas/views/app/logout.php', {
            method: 'POST',
            credentials: 'same-origin'
        });

        const data = await response.json();

        if (data.success) {
            window.location.href = 'http://localhost/rochas/login';
        } else {
            showToast('Erro ao sair', 'error');
        }
    } catch (error) {
        console.error('Erro no logout:', error);
        showToast('Erro ao conectar com o servidor', 'error');
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

// Função viewOrder atualizada com TODAS as informações
// ===============================
// PEDIDOS - VER DETALHES (VERSÃO CORRIGIDA)
// ===============================
async function viewOrder(id) {
    console.log('Carregando detalhes do pedido:', id);
    
    try {
        const response = await fetch(`/rochas/api/orders.php?id=${id}`);
        const pedido = await response.json();
        
        console.log('Pedido completo:', pedido);
        
        if (response.ok) {
            currentOrderId = id;
            
            // ========== CABEÇALHO ==========
            document.getElementById('order-id').textContent = pedido.id;
            
            // Badge de status no header
            const statusBadge = document.getElementById('order-status-badge');
            if (statusBadge) {
                statusBadge.textContent = getStatusText(pedido.status);
                statusBadge.className = `status-badge ${pedido.status}`;
            }
            
            // ========== CLIENTE ==========
            document.getElementById('order-client').textContent = pedido.cliente_nome || '-';
            document.getElementById('order-email').textContent = pedido.cliente_email || '-';
            
            // Telefone (se disponível no endereço)
            const phoneEl = document.getElementById('order-phone');
            if (phoneEl) {
                const phone = pedido.endereco?.telefone || 'Não informado';
                phoneEl.textContent = phone;
            }
            
            // ========== ENDEREÇO ==========
            let enderecoHtml = '';
            
            if (pedido.endereco && pedido.endereco.rua) {
                const end = pedido.endereco;
                enderecoHtml = `
                    <strong>${escapeHtml(end.nome || pedido.cliente_nome)}</strong><br>
                    ${end.telefone ? `📞 ${escapeHtml(end.telefone)}<br>` : ''}
                    📍 ${escapeHtml(end.rua)}, ${escapeHtml(end.numero)}${end.complemento ? ' - ' + escapeHtml(end.complemento) : ''}<br>
                    ${escapeHtml(end.bairro)} - ${escapeHtml(end.cidade)}/${escapeHtml(end.estado)}<br>
                    CEP: ${formatCEP(end.cep)}
                `;
            } else {
                enderecoHtml = `
                    <div style="text-align: center; padding: 20px; color: #9ca3af;">
                        <div style="font-size: 48px; margin-bottom: 12px;">📭</div>
                        <p>Endereço de entrega não cadastrado</p>
                    </div>
                `;
            }
            
            document.getElementById('order-address').innerHTML = enderecoHtml;
            
            // ========== ITENS DO PEDIDO ==========
            const itemsTable = document.getElementById('order-items');
            itemsTable.innerHTML = '';
            
            if (pedido.itens && pedido.itens.length > 0) {
                let subtotal = 0;
                
                pedido.itens.forEach(item => {
                    const precoUnitario = parseFloat(item.preco_unitario || 0);
                    const quantidade = parseInt(item.quantidade || 0);
                    const itemTotal = quantidade * precoUnitario;
                    subtotal += itemTotal;
                    
                    itemsTable.innerHTML += `
                        <tr>
                            <td>
                                ${item.produto_imagem ? 
                                    `<img src="/rochas/storage/images/${item.produto_imagem}" 
                                          alt="${escapeHtml(item.produto_nome || item.nome_produto)}" 
                                          class="product-img"
                                          onerror="this.style.display='none'">` 
                                    : ''}
                                <div class="product-info">
                                    <span class="product-name">${escapeHtml(item.produto_nome || item.nome_produto)}</span>
                                    ${item.produto_id ? `<span class="product-sku">ID: ${item.produto_id}</span>` : ''}
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="qty-badge">${quantidade}x</span>
                            </td>
                            <td style="text-align: right;">
                                <span class="price">${formatMoney(precoUnitario)}</span>
                                <span class="price-detail">Total: ${formatMoney(itemTotal)}</span>
                            </td>
                        </tr>
                    `;
                });
                
                // ========== RESUMO FINANCEIRO ==========
                const frete = parseFloat(pedido.valor_frete || 0);
                const totalPedido = parseFloat(pedido.total || 0);
                
                // Usar subtotal calculado se o do banco estiver zerado
                const subtotalFinal = parseFloat(pedido.subtotal || subtotal || 0);
                
                document.getElementById('order-subtotal').textContent = formatMoney(subtotalFinal);
                document.getElementById('order-shipping').textContent = formatMoney(frete);
                document.getElementById('order-total').textContent = formatMoney(totalPedido > 0 ? totalPedido : subtotalFinal + frete);
                
                // Informações de frete
                document.getElementById('shipping-type').textContent = pedido.tipo_frete || 'Não informado';
                document.getElementById('shipping-days').textContent = pedido.prazo_frete || '0';
                
            } else {
                itemsTable.innerHTML = `
                    <tr>
                        <td colspan="3" class="empty-state">
                            <div style="font-size: 48px;">📦</div>
                            <p>Nenhum item encontrado neste pedido</p>
                        </td>
                    </tr>
                `;
                
                // Mesmo sem itens, mostrar valores do pedido
                document.getElementById('order-subtotal').textContent = formatMoney(parseFloat(pedido.subtotal || 0));
                document.getElementById('order-shipping').textContent = formatMoney(parseFloat(pedido.valor_frete || 0));
                document.getElementById('order-total').textContent = formatMoney(parseFloat(pedido.total || 0));
                document.getElementById('shipping-type').textContent = pedido.tipo_frete || '-';
                document.getElementById('shipping-days').textContent = pedido.prazo_frete || '0';
            }
            
            // ========== STATUS ==========
            document.getElementById('order-status').value = pedido.status;
            
            // ========== ABRIR MODAL ==========
            const modal = document.getElementById('order-modal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        } else {
            showError(pedido.error || 'Erro ao carregar pedido');
        }
    } catch (error) {
        console.error('Erro:', error);
        showError('Erro ao carregar pedido: ' + error.message);
    }
}

// ===============================
// FUNÇÕES AUXILIARES
// ===============================

function getStatusText(status) {
    const statusMap = {
        'pending': 'Pendente',
        'processing': 'Processando',
        'shipped': 'Enviado',
        'delivered': 'Entregue',
        'cancelled': 'Cancelado',
        'pendente': 'Pendente',
        'processando': 'Processando',
        'enviado': 'Enviado',
        'entregue': 'Entregue',
        'cancelado': 'Cancelado'
    };
    return statusMap[status] || status;
}

function formatCEP(cep) {
    if (!cep) return '';
    // Remove tudo que não é número
    const numbers = String(cep).replace(/\D/g, '');
    // Formata: 12345-678
    if (numbers.length === 8) {
        return numbers.replace(/(\d{5})(\d{3})/, '$1-$2');
    }
    return cep;
}

function formatMoney(value) {
    if (!value && value !== 0) return 'R$ 0,00';
    
    const num = parseFloat(value);
    if (isNaN(num)) return 'R$ 0,00';
    
    return num.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Adicione esta função ao seu app.js existente (substituindo a viewOrder antiga)
// E também adicione as funções auxiliares (getStatusText, formatCEP, formatMoney)

// Adicione esta função ao seu app.js existente (substituindo a viewOrder antiga)

console.log('✅ app.js carregado com sucesso');