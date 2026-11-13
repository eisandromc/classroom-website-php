-- WEBSHOES | Estrutura do banco MySQL 8+ / MariaDB
-- Execute este arquivo antes de dados.sql.
CREATE DATABASE IF NOT EXISTS webshoes
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE webshoes;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS usuarios_administrativos;
DROP TABLE IF EXISTS produtos;
DROP TABLE IF EXISTS categorias;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE categorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    descricao VARCHAR(255) NOT NULL,
    cor VARCHAR(7) NOT NULL DEFAULT '#F1F1F1',
    ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE produtos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT UNSIGNED NOT NULL,
    nome VARCHAR(120) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    marca VARCHAR(70) NOT NULL,
    descricao_curta VARCHAR(250) NOT NULL,
    descricao TEXT NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    preco_anterior DECIMAL(10,2) NULL,
    imagem VARCHAR(100) NOT NULL,
    etiqueta VARCHAR(30) NOT NULL DEFAULT '',
    cor VARCHAR(80) NOT NULL,
    tamanhos VARCHAR(80) NOT NULL,
    destaque TINYINT(1) NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_produtos_categorias
        FOREIGN KEY (categoria_id) REFERENCES categorias (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_preco_positivo CHECK (preco >= 0),
    INDEX idx_produtos_categoria_ativo (categoria_id, ativo),
    INDEX idx_produtos_destaque (destaque, ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Credenciais administrativas armazenadas somente como hash de senha.
CREATE TABLE IF NOT EXISTS usuarios_administrativos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(60) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
