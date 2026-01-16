/* ===============================
   ESTADO GLOBAL
================================ */
let cart = JSON.parse(localStorage.getItem('redemac-cart')) || [];
let freteSelecionado = 0;

/* ===============================
   INIT
================================ */
document.addEventListener('DOMContentLoaded', () => {
    renderSummary();
});

/* ===============================
   RESUMO
================================ */
function renderSummary() {
    const container = document.getElementById('summary-items');
    container.innerHTML = '';

    cart.forEach(item => {
        const imageUrl = item.imagem
            ? `/rochas/storage/images/${item.imagem}`
            : `/rochas/assets/img/placeholder.png`;

        container.innerHTML += `
            <div class="summary-item">
                <img 
                    src="${imageUrl}" 
                    alt="${item.nome}" 
                    class="summary-item-image"
                >

                <div class="summary-item-details">
                    <strong>${item.nome}</strong>
                    <div class="summary-item-qty">Qtd: ${item.quantity}</div>
                </div>

                <div class="summary-item-price">
                    ${formatPrice(item.preco * item.quantity)}
                </div>
            </div>
        `;
    });

    atualizarTotal();
}


/* ===============================
   TOTAL
================================ */
function getSubtotal() {
    return cart.reduce((s, i) => s + i.preco * i.quantity, 0);
}

function atualizarTotal() {
    document.getElementById('summary-price').textContent =
        formatPrice(getSubtotal() + freteSelecionado);
}

/* ===============================
   CALCULAR FRETE
================================ */
async function calcularFrete() {
    const cep = document.getElementById('cep').value.replace(/\D/g, '');

    if (cep.length !== 8) {
        alert('Informe um CEP válido');
        return;
    }

    const response = await fetch('/rochas/api/frete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            cep,
            produtos: cart.map(item => ({
                peso: item.peso || 1,
                largura: item.largura || 15,
                altura: item.altura || 10,
                comprimento: item.comprimento || 20,
                quantidade: item.quantity
            }))
        })
    });

    const data = await response.json();

    if (!Array.isArray(data)) {
        console.error(data);
        alert(data.error || 'Erro ao calcular frete');
        return;
    }

    const fretesValidos = data.filter(f => !f.error && f.price);

    if (!fretesValidos.length) {
        alert('Nenhuma opção de frete disponível');
        return;
    }

    renderFreteOpcoes(fretesValidos);
}

/* ===============================
   RENDER FRETES
================================ */
function renderFreteOpcoes(fretes) {
    const container = document.getElementById('frete-opcoes');
    container.innerHTML = '';

    fretes.forEach(frete => {
        container.innerHTML += `
            <label class="frete-opcao">
                <input type="radio" name="frete"
                    onchange="selecionarFrete(${frete.price})">
                <strong>${frete.company.name}</strong> – ${frete.name}<br>
                ${formatPrice(frete.price)} • ${frete.delivery_time} dias
            </label>
        `;
    });
}

function selecionarFrete(valor) {
    freteSelecionado = Number(valor);
    atualizarTotal();
}

/* ===============================
   FINALIZAR
================================ */
function finalizePurchase() {
    const nome = document.getElementById('nome').value;
    const telefone = document.getElementById('telefone').value;

    if (!nome || !telefone) {
        alert('Preencha seus dados');
        return;
    }

    let msg = `🧾 *Pedido Redemac*\n\n`;
    msg += `Cliente: ${nome}\nTelefone: ${telefone}\n\n`;

    cart.forEach(i => {
        msg += `- ${i.nome} x${i.quantity}\n`;
    });

    msg += `\nFrete: ${formatPrice(freteSelecionado)}`;
    msg += `\nTotal: ${formatPrice(getSubtotal() + freteSelecionado)}`;

    window.open(`https://wa.me/55SEUNUMERO?text=${encodeURIComponent(msg)}`);
    localStorage.removeItem('redemac-cart');
}

/* ===============================
   UTIL
================================ */
function formatPrice(v) {
    return v.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    });
}
