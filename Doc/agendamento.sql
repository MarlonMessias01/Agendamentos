CREATE DATABASE agendamento;

USE agendamento;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

CREATE TABLE servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_servico VARCHAR(100) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL
);

CREATE TABLE agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_servico INT NOT NULL,
    data_agendamento DATE NOT NULL,
    horario TIME NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'disponível',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_servico) REFERENCES servicos(id)
);
INSERT INTO servicos (nome_servico, valor) VALUES
('Corte de Cabelo', 30.00),
('Barba', 20.00),
('Corte e Barba', 45.00),
('Sobrancelhas', 15.00),
('Hidratação Capilar', 40.00),
('Pintura de Cabelo', 70.00);
ALTER TABLE usuarios ADD COLUMN tipo_usuario ENUM('admin', 'usuario') NOT NULL DEFAULT 'usuario';
ALTER TABLE usuarios ADD COLUMN stts_usuario ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo';