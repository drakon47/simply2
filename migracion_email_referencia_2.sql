-- Script de migración para agregar el campo email_referencia_2 a la tabla centros
-- Ejecutar este script si ya tienes una base de datos existente

USE drakon_simply;

-- Agregar el nuevo campo email_referencia_2 a la tabla centros
ALTER TABLE centros 
ADD COLUMN email_referencia_2 VARCHAR(255) NULL 
AFTER email_referencia;

