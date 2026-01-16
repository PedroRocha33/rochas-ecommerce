# 🗄️ Banco de Dados – ROCHAS E-COMMERCE

Este documento descreve a **estrutura do banco de dados** do projeto **Rochas E-commerce**, explicando o objetivo de cada tabela, seus relacionamentos e regras de negócio.

O banco foi modelado para suportar:
- Usuários com níveis (Cliente, Gerente e Admin)
- Produtos e categorias
- Carrinho de compras
- Pedidos e pagamentos
- Endereços e envios
- Escalabilidade futura

---

## 📌 Informações Gerais

- **SGBD**: MySQL
- **Charset**: utf8mb4
- **Collation**: utf8mb4_general_ci
- **Arquivo SQL**: `database.sql`
- **Banco**: `ecommerce`

---

## 👤 Tabela: `usuarios`

Armazena todos os usuários do sistema, independentemente do nível de acesso.

### Campos principais:
- `id`: Identificador único
- `nome`: Nome do usuário
- `email`: Email único
- `senha`: Hash da senha (password_hash)
- `nivel`:
  - `1` → Cliente
  - `2` → Gerente
  - `3` → Admin
- `ativo`: Define se o usuário pode acessar o sistema
- `criado_em`: Data de criação do registro

### Observações:
- A autenticação é baseada nesta tabela.
- O campo `nivel` controla o acesso às áreas do sistema.

---

## 🏠 Tabela: `enderecos`

Armazena os endereços dos usuários (principalmente clientes).

### Relacionamento:
- **N:1** com `usuarios`

### Campos principais:
- `usuario_id`: Usuário dono do endereço
- `principal`: Define o endereço padrão do usuário

---

## 📂 Tabela: `categorias`

Responsável por organizar os produtos.

### Campos principais:
- `nome`: Nome da categoria
- `slug`: URL amigável
- `ativa`: Define se a categoria está visível

---

## 📦 Tabela: `produtos`

Armazena os produtos disponíveis no e-commerce.

### Relacionamento:
- **N:1** com `categorias`

### Campos principais:
- `nome`: Nome do produto
- `descricao`: Descrição detalhada
- `preco`: Preço atual
- `estoque`: Quantidade disponível
- `imagem`: Caminho da imagem
- `ativo`: Produto visível ou não

---

## 🛒 Tabela: `carrinhos`

Representa o carrinho de compras do usuário.

### Relacionamento:
- **1:1** com `usuarios`

### Observações:
- Opcional, pois o carrinho também pode ser mantido via sessão.
- Permite recuperar carrinho após logout.

---

## 🧾 Tabela: `carrinho_itens`

Itens adicionados ao carrinho.

### Relacionamentos:
- **N:1** com `carrinhos`
- **N:1** com `produtos`

### Campos principais:
- `quantidade`: Quantidade do produto no carrinho

---

## 📑 Tabela: `pedidos`

Registra as compras finalizadas.

### Relacionamento:
- **N:1** com `usuarios`

### Status comuns:
- `pendente`
- `pago`
- `enviado`
- `cancelado`

---

## 📋 Tabela: `pedido_itens`

Produtos que compõem um pedido.

### Relacionamentos:
- **N:1** com `pedidos`
- **N:1** com `produtos`

### Observações:
- O preço é salvo para manter histórico mesmo que o produto mude de valor.

---

## 💳 Tabela: `pagamentos`

Armazena informações de pagamento.

### Relacionamento:
- **1:1** com `pedidos`

### Campos principais:
- `metodo`: Forma de pagamento (pix, cartão, boleto, etc.)
- `status`: Situação do pagamento
- `referencia`: Código externo (ex: Mercado Pago)

---

## 🚚 Tabela: `envios`

Controle de envio e rastreamento.

### Relacionamento:
- **1:1** com `pedidos`

---

## ⭐ Tabela: `avaliacoes`

Avaliações dos produtos pelos usuários.

### Relacionamentos:
- **N:1** com `usuarios`
- **N:1** com `produtos`

### Campos principais:
- `nota`: Avaliação numérica
- `comentario`: Texto livre

---

## 🔐 Segurança

- Senhas armazenadas com `password_hash()`
- Uso de `PDO` para evitar SQL Injection
- Relacionamentos garantidos com `FOREIGN KEY`

---

## 📊 Diagrama (Resumo Conceitual)

usuarios
├── enderecos
├── carrinhos
│ └── carrinho_itens ── produtos ── categorias
└── pedidos
├── pedido_itens ── produtos
├── pagamentos
└── envios


---

## 🚀 Expansões Futuras

- Cupons de desconto
- Histórico de alterações
- Logs administrativos
- Produtos com variações
- Multiloja

---

## 📄 Observação Final

Este banco foi projetado para ser **simples, seguro e escalável**, atendendo desde pequenos projetos até e-commerces mais robustos.

