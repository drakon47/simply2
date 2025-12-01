-- Tabla para almacenar documentos requeridos por proyecto
CREATE TABLE IF NOT EXISTS docu_requerida_proyecto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(500) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar los documentos requeridos
INSERT INTO docu_requerida_proyecto (titulo) VALUES
('Protocolo'),
('Consentimiento');

