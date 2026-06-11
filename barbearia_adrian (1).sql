SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS barbearia_adrian_souza
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE barbearia_adrian_souza;

DROP TABLE IF EXISTS cliente_servico;
DROP TABLE IF EXISTS agendamentos;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS servicos;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE servicos (
  id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome  VARCHAR(100) NOT NULL,
  preco DECIMAL(8,2) NOT NULL,
  ativo TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE clientes (
  id       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome     VARCHAR(100) NOT NULL,
  telefone VARCHAR(20)      NULL,
  email    VARCHAR(150)     NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agendamentos (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente_nome VARCHAR(100) NOT NULL,
  servico_id   INT UNSIGNED NOT NULL,
  data_hora    DATETIME     NOT NULL,
  status       ENUM('pendente','confirmado','cancelado') NOT NULL DEFAULT 'pendente',
  criado_em    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (servico_id) REFERENCES servicos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cliente_servico (
  cliente_id INT UNSIGNED NOT NULL,
  servico_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (cliente_id, servico_id),
  FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  FOREIGN KEY (servico_id) REFERENCES servicos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO servicos (nome, preco) VALUES
('Corte Premium',      45.00),
('Barba Premium',      35.00),
('Combo Completo',     70.00),
('Plano Profissional', 120.00);

INSERT INTO clientes (nome, telefone, email) VALUES
('Felipe Martins',   '44999990001', 'felipe@email.com'),
('Gustavo Henrique', '44999990002', 'gustavo@email.com'),
('Lucas Ferreira',   '44999990003', 'lucas@email.com');

INSERT INTO cliente_servico (cliente_id, servico_id) VALUES
(1, 1),
(1, 2),
(2, 3),
(3, 1),
(3, 4);

INSERT INTO agendamentos (cliente_nome, servico_id, data_hora, status) VALUES
('Felipe Martins',   1, '2025-07-10 09:00:00', 'confirmado'),
('Gustavo Henrique', 3, '2025-07-10 10:30:00', 'pendente'),
('Lucas Ferreira',   4, '2025-07-11 14:00:00', 'confirmado');

