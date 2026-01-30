async function finalizarCompraMP() {
    // ✅ Validar carrinho
    if (!cart || cart.length === 0) {
        alert('Carrinho vazio');
        return;
    }

    // ✅ Validar campos do formulário
    const camposObrigatorios = ['nome', 'telefone', 'email', 'cep', 'rua', 'numero', 'bairro', 'cidade', 'estado'];
    
    for (const campo of camposObrigatorios) {
        const elemento = document.getElementById(campo);
        if (!elemento || !elemento.value.trim()) {
            alert(`Campo "${campo}" é obrigatório`);
            elemento?.focus();
            return;
        }
    }

    const cliente = {
        nome: document.getElementById('nome').value.trim(),
        telefone: document.getElementById('telefone').value.trim(),
        email: document.getElementById('email').value.trim()
    };

    const endereco = {
        cep: document.getElementById('cep').value.trim(),
        rua: document.getElementById('rua').value.trim(),
        numero: document.getElementById('numero').value.trim(),
        complemento: document.getElementById('complemento')?.value.trim() || '',
        bairro: document.getElementById('bairro').value.trim(),
        cidade: document.getElementById('cidade').value.trim(),
        estado: document.getElementById('estado').value.trim()
    };

    // ✅ Calcular total
    const totalProdutos = cart.reduce((total, item) => {
        return total + (parseFloat(item.preco) * parseInt(item.quantity));
    }, 0);

    const frete = parseFloat(window.freteSelecionado) || 0;
    const total = totalProdutos + frete;

    // ✅ Montar itens - FORMATO CORRETO
    const itens = cart.map(item => ({
        produto_id: item.id,
        nome: item.nome || item.title || 'Produto sem nome',
        quantidade: parseInt(item.quantity) || 1,
        preco: parseFloat(item.preco) || 0
    }));

    // ✅ Validar itens
    for (const item of itens) {
        if (!item.nome || item.nome === '') {
            alert('Erro: produto sem nome no carrinho');
            console.error('Item inválido:', item);
            return;
        }
        
        if (item.quantidade < 1) {
            alert('Erro: quantidade inválida');
            console.error('Item inválido:', item);
            return;
        }
        
        if (item.preco <= 0) {
            alert('Erro: preço inválido');
            console.error('Item inválido:', item);
            return;
        }
    }

    const pedido = {
        cliente,
        endereco,
        frete: frete,
        total: total,
        itens: itens
    };

    // ✅ Log para debug
    console.log('Enviando pedido:', JSON.stringify(pedido, null, 2));

    try {
        // ✅ Mostrar loading
        const btnFinalizar = document.querySelector('button[onclick*="finalizarCompraMP"]');
        if (btnFinalizar) {
            btnFinalizar.disabled = true;
            btnFinalizar.textContent = 'Processando...';
        }

        const response = await fetch('http://localhost/rochas/api/checkout/create.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(pedido)
        });

        // ✅ Log da resposta
        console.log('Status HTTP:', response.status);
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Resposta não é JSON:', text);
            alert('Erro no servidor. Verifique o console para mais detalhes.');
            return;
        }

        const data = await response.json();
        console.log('Resposta do servidor:', data);

        if (!data.success) {
            alert('Erro: ' + (data.error || 'Erro desconhecido'));
            console.error('Erro detalhado:', data);
            return;
        }

        // ✅ Verificar se tem init_point
        if (!data.init_point) {
            alert('Erro: link de pagamento não foi gerado');
            console.error('Resposta sem init_point:', data);
            return;
        }

        console.log('Redirecionando para:', data.init_point);
        
        // Redireciona para Mercado Pago
        window.location.href = data.init_point;

    } catch (err) {
        console.error('Erro ao processar pagamento:', err);
        alert('Erro ao processar pagamento. Verifique sua conexão e tente novamente.');
    } finally {
        // ✅ Restaurar botão
        const btnFinalizar = document.querySelector('button[onclick*="finalizarCompraMP"]');
        if (btnFinalizar) {
            btnFinalizar.disabled = false;
            btnFinalizar.textContent = 'Finalizar Compra';
        }
    }
}

function calcularTotalComFrete() {
    const totalProdutos = cart.reduce((total, item) => {
        return total + (parseFloat(item.preco) * parseInt(item.quantity));
    }, 0);

    return totalProdutos + (parseFloat(window.freteSelecionado) || 0);
}