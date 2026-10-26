CREATE DATABASE IF NOT EXISTS loja_virtual;
USE loja_virtual;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    estoque INT NOT NULL DEFAULT 0
);

INSERT INTO usuarios (nome, email, senha) VALUES 
('Gleisson Silva', 'gleisson@teste.com', '$2y$10$e8oY2XGfZ0a4Q1Hk5b1hEeZ6r8K6m9sU7G.YQk4Q9Oa4d0Y0P2uTu');

INSERT INTO produtos (nome, preco, estoque) VALUES 
('Camisa Esportiva', 89.90, 15),
('Bola de Futebol', 120.00, 8),
('Chuteira Society', 249.99, 4),
('Meião de Jogo', 25.00, 0);