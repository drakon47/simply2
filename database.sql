-- Base de datos para proyecto Simply
CREATE DATABASE IF NOT EXISTS drakon_simply CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE drakon_simply;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    rol ENUM('CENTRO', 'LABORATORIO', 'BUDDY', 'ADMIN') NOT NULL,
    idEntidad INT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de centros
CREATE TABLE centros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    direccion VARCHAR(500) NOT NULL,
    localidad VARCHAR(100) NOT NULL,
    provincia VARCHAR(100) NOT NULL,
    pais VARCHAR(100) NOT NULL,
    email_referencia VARCHAR(255),
    email_referencia_2 VARCHAR(255),
    telefono VARCHAR(50),
    fecha_alta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de laboratorios
CREATE TABLE laboratorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    pais VARCHAR(100) NOT NULL,
    fecha_alta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de proyectos
CREATE TABLE proyectos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    patrocinante_id INT NOT NULL,
    descripcion TEXT,
    fecha_alta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (patrocinante_id) REFERENCES laboratorios(id)
);

-- Tabla de pacientes
CREATE TABLE pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(255),
    telefono1 VARCHAR(50),
    telefono2 VARCHAR(50),
    domicilio_calle VARCHAR(255),
    domicilio_numero VARCHAR(20),
    domicilio_piso VARCHAR(20),
    domicilio_depto VARCHAR(20),
    domicilio_localidad VARCHAR(100),
    domicilio_provincia VARCHAR(100),
    familiar_contacto VARCHAR(255),
    telefono_familiar VARCHAR(50),
    consentimiento_firmado ENUM('NO', 'SI') DEFAULT 'NO',
    comentarios TEXT,
    proyecto_id INT,
    centro_id INT,
    fecha_alta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id),
    FOREIGN KEY (centro_id) REFERENCES centros(id)
);

-- Tabla de eventos de tratamiento
CREATE TABLE eventos_tratamiento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proyecto_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    dias_desde_inicio INT NOT NULL,
    tipo_evento ENUM('Presencial', 'Virtual', 'Llamado', 'Otro') NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id)
);

-- Insertar usuarios de prueba para cada rol
INSERT INTO usuarios (email, password, nombre, apellido, rol, idEntidad) VALUES
('centro@test.com', MD5('123456'), 'María', 'González', 'CENTRO', 1),
('laboratorio@test.com', MD5('123456'), 'Carlos', 'Rodríguez', 'LABORATORIO', 1),
('buddy@test.com', MD5('123456'), 'Ana', 'Martín', 'BUDDY', NULL),
('admin@test.com', MD5('123456'), 'Administrador', 'Sistema', 'ADMIN', NULL);

-- Insertar 10 centros de prueba
INSERT INTO centros (nombre, direccion, localidad, provincia, pais, email_referencia, telefono) VALUES
('Centro Médico San Juan', 'Av. Principal 123', 'Madrid', 'Madrid', 'España', 'info@sanjuancm.com', '+34 91 123 4567'),
('Hospital General Barcelona', 'Calle Mayor 456', 'Barcelona', 'Barcelona', 'España', 'contacto@hgb.es', '+34 93 234 5678'),
('Clínica Valencia Norte', 'Plaza España 789', 'Valencia', 'Valencia', 'España', 'info@clinicanorte.com', '+34 96 345 6789'),
('Centro de Salud Sevilla', 'Av. Andalucía 321', 'Sevilla', 'Sevilla', 'España', 'centro@sevillasalud.es', '+34 95 456 7890'),
('Hospital Bilbao Central', 'Gran Vía 654', 'Bilbao', 'Vizcaya', 'España', 'info@hbilbao.com', '+34 94 567 8901'),
('Clínica Málaga Sur', 'Calle Larios 987', 'Málaga', 'Málaga', 'España', 'contacto@malagasur.com', '+34 95 678 9012'),
('Centro Médico Zaragoza', 'Paseo Independencia 147', 'Zaragoza', 'Zaragoza', 'España', 'info@cmzaragoza.es', '+34 97 789 0123'),
('Hospital Murcia Este', 'Av. Juan Carlos I 258', 'Murcia', 'Murcia', 'España', 'contacto@hmurcia.com', '+34 96 890 1234'),
('Clínica Palma Mallorca', 'Paseo Marítimo 369', 'Palma', 'Baleares', 'España', 'info@palmahealth.com', '+34 97 901 2345'),
('Centro Sanitario Las Palmas', 'Calle Triana 741', 'Las Palmas', 'Las Palmas', 'España', 'centro@lpalmas.es', '+34 92 012 3456');

-- Insertar 10 laboratorios de prueba
INSERT INTO laboratorios (nombre, pais) VALUES
('Laboratorio Roche España', 'España'),
('Pfizer Research Center', 'Estados Unidos'),
('Novartis Laboratories', 'Suiza'),
('Sanofi Research Institute', 'Francia'),
('Merck Research Labs', 'Alemania'),
('Johnson & Johnson Innovation', 'Estados Unidos'),
('GSK Research Center', 'Reino Unido'),
('Bayer Healthcare', 'Alemania'),
('AstraZeneca Research', 'Reino Unido'),
('Boehringer Ingelheim', 'Alemania');

-- Tabla de relación centros-proyectos
CREATE TABLE centros_proyectos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    centro_id INT NOT NULL,
    proyecto_id INT NOT NULL,
    fecha_asociacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (centro_id) REFERENCES centros(id),
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id),
    UNIQUE KEY unique_centro_proyecto (centro_id, proyecto_id)
);

-- Insertar algunos proyectos de prueba
INSERT INTO proyectos (nombre, patrocinante_id, descripcion) VALUES
('Estudio Cardiovascular CR-2024', 1, 'Investigación sobre nuevos tratamientos cardiovasculares'),
('Ensayo Oncológico ONC-001', 2, 'Estudio de fase III para tratamiento de cáncer de pulmón'),
('Investigación Diabetes DIA-2024', 3, 'Nuevos protocolos para manejo de diabetes tipo 2'),
('Estudio Neurología NEU-001', 4, 'Investigación sobre enfermedades neurodegenerativas'),
('Ensayo Inmunología IMM-2024', 5, 'Estudio de nuevas terapias inmunológicas');
