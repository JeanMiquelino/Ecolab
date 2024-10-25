CREATE DATABASE network_monitoring;

USE network_monitoring;

-- Tabela para armazenar hosts ativos na rede
CREATE TABLE active_hosts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(15) NOT NULL,
    mac VARCHAR(17) NOT NULL,
    ttl INT,
    os VARCHAR(50),
    scan_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela para armazenar portas abertas
CREATE TABLE open_ports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(15) NOT NULL,
    port INT NOT NULL,
    service VARCHAR(50),
    scan_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    tel VARCHAR(14) NOT NULL,
    pass VARCHAR(255) NOT NULL
)
