CREATE DATABASE IF NOT EXISTS webshoes_identidade CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE webshoes_identidade;
DROP TABLE IF EXISTS usuarios_administrativos;
CREATE TABLE usuarios_administrativos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(60) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
