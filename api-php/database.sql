-- database.sql
-- Ejecuta este script en tu gestor de base de datos MySQL/MariaDB (ej. PhpMyAdmin, DBeaver, etc.)

CREATE DATABASE IF NOT EXISTS app_db;
USE app_db;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserción de usuarios iniciales (Primera carga y objeto)
-- Para facilitar las pruebas, las contraseñas están en texto plano.
-- En un entorno de producción, DEBES utilizar password_hash() en PHP.
INSERT IGNORE INTO usuarios (username, password, nombre, email) VALUES 
('admin', 'admin123', 'Administrador del Sistema', 'admin@ejemplo.com'),
('usuario1', 'secreta1', 'Juan Perez', 'juan@ejemplo.com'),
('test', 'test123', 'Usuario de Prueba', 'test@ejemplo.com');
