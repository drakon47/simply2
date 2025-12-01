-- Tabla para almacenar documentos requeridos por centro
CREATE TABLE IF NOT EXISTS docu_requerida_centro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(500) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar los documentos requeridos
INSERT INTO docu_requerida_centro (titulo) VALUES
('Certificado de Habilitación del centro'),
('Certificado en Buenas Practicas Clinicas Inv. Principal'),
('Certificado de Entrenamiento en regulacion local'),
('Curriculum Vitae actualizado Investigador Principal'),
('Curriculum Vitae actualizado de Staff'),
('Matricula Profesional vigente Investigador Principal'),
('Matricula Profesional Vigente STAFF del Centro'),
('Contrato vigente con Institucion para Internacion'),
('Contrato vigente para traslado de paciente'),
('Sistema de Control de temperatura diario de medicacion ya establecido'),
('chequeo del Procedimiento establecido ante corte de electricidad'),
('Miembros y SOPs del Comité de Etica Institucional disponible'),
('SOPs del Centro de investigacion disponibles');

