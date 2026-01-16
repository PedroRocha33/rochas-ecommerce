-- =========================================
-- BANCO DE DADOS: ECOMMERCE
-- =========================================

CREATE DATABASE IF NOT EXISTS ecommerce
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE ecommerce;

-- =========================================
-- USUÁRIOS (cliente, gerente, admin)
-- =========================================
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  nivel TINYINT NOT NULL COMMENT '1=cliente, 2=gerente, 3=admin',
  ativo BOOLEAN DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================================
-- ENDEREÇOS
-- =========================================
CREATE TABLE enderecos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  rua VARCHAR(150),
  numero VARCHAR(20),
  bairro VARCHAR(100),
  cidade VARCHAR(100),
  estado VARCHAR(50),
  cep VARCHAR(20),
  principal BOOLEAN DEFAULT 0,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- =========================================
-- CATEGORIAS
-- =========================================
CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  slug VARCHAR(100) UNIQUE,
  ativa BOOLEAN DEFAULT 1
);

-- =========================================
-- PRODUTOS
-- =========================================
CREATE TABLE produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  slug VARCHAR(150) UNIQUE,
  descricao TEXT,
  preco DECIMAL(10,2) NOT NULL,
  estoque INT DEFAULT 0,
  imagem VARCHAR(255),
  categoria_id INT,
  ativo BOOLEAN DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- =========================================
-- CARRINHO
-- =========================================
CREATE TABLE carrinhos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE carrinho_itens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  carrinho_id INT NOT NULL,
  produto_id INT NOT NULL,
  quantidade INT DEFAULT 1,
  FOREIGN KEY (carrinho_id) REFERENCES carrinhos(id),
  FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- =========================================
-- PEDIDOS
-- =========================================
CREATE TABLE pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  total DECIMAL(10,2),
  status VARCHAR(50) DEFAULT 'pendente',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE pedido_itens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  produto_id INT NOT NULL,
  quantidade INT NOT NULL,
  preco DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
  FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- =========================================
-- PAGAMENTOS
-- =========================================
CREATE TABLE pagamentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  metodo VARCHAR(50),
  status VARCHAR(50),
  referencia VARCHAR(100),
  valor DECIMAL(10,2),
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
);

-- =========================================
-- ENVIOS
-- =========================================
CREATE TABLE envios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  status VARCHAR(50),
  codigo_rastreio VARCHAR(100),
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
);

-- =========================================
-- AVALIAÇÕES
-- =========================================
CREATE TABLE avaliacoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  produto_id INT NOT NULL,
  usuario_id INT NOT NULL,
  nota TINYINT,
  comentario TEXT,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (produto_id) REFERENCES produtos(id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- =========================================
-- DADOS INICIAIS (ADMIN PADRÃO)
-- SENHA: 123456
-- =========================================
INSERT INTO usuarios (nome, email, senha, nivel)
VALUES (
  'Administrador',
  'admin@site.com',
  '$2y$10$uFJvFQFQ3fZ4o9XlZ8z3GuR9z0r4XK7mP2qvF4H6B0z7P9y8r2E9O',
  3
);

-- =========================================
-- CATEGORIAS EXEMPLO
-- =========================================
INSERT INTO categorias (nome, slug) VALUES
('Tecnologia', 'tecnologia'),
('Casa', 'casa'),
('Móveis', 'moveis');

-- =========================================
-- PRODUTO EXEMPLO
-- =========================================
INSERT INTO produtos (nome, slug, descricao, preco, estoque, categoria_id)
VALUES (
  'Produto Exemplo',
  'produto-exemplo',
  'Descrição do produto exemplo',
  199.90,
  10,
  1
);
