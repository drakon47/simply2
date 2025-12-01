-- Tabla para almacenar documentos subidos por cada proyecto
CREATE TABLE IF NOT EXISTS documentos_proyecto_subidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    docu_requerida_proyecto_id INT NOT NULL,
    proyecto_id INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    fecha_vencimiento DATE NULL,
    fecha_alerta_roja DATE NULL,
    fecha_alerta_amarilla DATE NULL,
    responsable VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (docu_requerida_proyecto_id) REFERENCES docu_requerida_proyecto(id) ON DELETE CASCADE,
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
    INDEX idx_docu_requerida (docu_requerida_proyecto_id),
    INDEX idx_proyecto (proyecto_id),
    INDEX idx_activo (activo),
    INDEX idx_fecha_vencimiento (fecha_vencimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

