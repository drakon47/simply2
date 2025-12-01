<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getUserInfo();

// Solo usuarios ADMIN pueden acceder
if ($user['rol'] !== 'ADMIN') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Configurar respuesta JSON
header('Content-Type: application/json');

if ($_POST) {
    $accion = $_POST['accion'] ?? '';
    $centro_id = $_POST['centro_id'] ?? null;
    $proyecto_id = $_POST['proyecto_id'] ?? null;
    
    if (!$centro_id || !$proyecto_id) {
        echo json_encode(['success' => false, 'message' => 'Parámetros faltantes']);
        exit();
    }
    
    try {
        if ($accion === 'asociar') {
            // Verificar que el centro y proyecto existen
            $stmt = $db->prepare("SELECT id FROM centros WHERE id = ? AND activo = 1");
            $stmt->execute([$centro_id]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Centro no encontrado']);
                exit();
            }
            
            $stmt = $db->prepare("SELECT id FROM proyectos WHERE id = ? AND activo = 1");
            $stmt->execute([$proyecto_id]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Proyecto no encontrado']);
                exit();
            }
            
            // Verificar si ya existe la asociación
            $stmt = $db->prepare("SELECT id FROM centros_proyectos WHERE centro_id = ? AND proyecto_id = ?");
            $stmt->execute([$centro_id, $proyecto_id]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El proyecto ya está asociado a este centro']);
                exit();
            }
            
            // Crear la asociación
            $stmt = $db->prepare("INSERT INTO centros_proyectos (centro_id, proyecto_id) VALUES (?, ?)");
            $stmt->execute([$centro_id, $proyecto_id]);
            
            echo json_encode(['success' => true, 'message' => 'Proyecto asociado correctamente']);
            
        } elseif ($accion === 'desasociar') {
            // Verificar que existe la asociación
            $stmt = $db->prepare("SELECT id FROM centros_proyectos WHERE centro_id = ? AND proyecto_id = ? AND activo = 1");
            $stmt->execute([$centro_id, $proyecto_id]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'La asociación no existe']);
                exit();
            }
            
            // Desactivar la asociación (soft delete)
            $stmt = $db->prepare("UPDATE centros_proyectos SET activo = 0 WHERE centro_id = ? AND proyecto_id = ?");
            $stmt->execute([$centro_id, $proyecto_id]);
            
            echo json_encode(['success' => true, 'message' => 'Proyecto desasociado correctamente']);
            
        } else {
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
