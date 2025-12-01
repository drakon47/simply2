-- Tabla para almacenar documentos subidos por cada centro
CREATE TABLE IF NOT EXISTS documentos_centro_subidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    docu_requerida_centro_id INT NOT NULL,
    centro_id INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    fecha_vencimiento DATE NULL,
    fecha_alerta_roja DATE NULL,
    fecha_alerta_amarilla DATE NULL,
    responsable VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (docu_requerida_centro_id) REFERENCES docu_requerida_centro(id) ON DELETE CASCADE,
    FOREIGN KEY (centro_id) REFERENCES centros(id) ON DELETE CASCADE,
    INDEX idx_docu_requerida (docu_requerida_centro_id),
    INDEX idx_centro (centro_id),
    INDEX idx_activo (activo),
    INDEX idx_fecha_vencimiento (fecha_vencimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

