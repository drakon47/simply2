<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getUserInfo();

// Solo usuarios CENTRO pueden acceder
if ($user['rol'] !== 'CENTRO') {
    header('Location: ../index.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$documento_id = $_GET['id'] ?? null;
$proyecto_id = $_GET['proyecto_id'] ?? null;
$centro_id = $user['identidad'];

if (!$documento_id || !$proyecto_id) {
    header('Location: estudio.php?id=' . ($proyecto_id ?? '') . '&error_doc=' . urlencode('ID de documento o proyecto no válido'));
    exit();
}

try {
    // Verificar que el proyecto está asociado al centro
    $stmt = $db->prepare("
        SELECT p.id 
        FROM proyectos p 
        INNER JOIN centros_proyectos cp ON p.id = cp.proyecto_id
        WHERE p.id = ? AND cp.centro_id = ? AND p.activo = 1 AND cp.activo = 1
    ");
    $stmt->execute([$proyecto_id, $centro_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Proyecto no encontrado o no tienes permiso');
    }
    
    // Obtener información del documento para verificar que pertenece al proyecto
    $stmt = $db->prepare("
        SELECT * FROM documentos_proyecto_subidos 
        WHERE id = ? AND proyecto_id = ? AND activo = 1
    ");
    $stmt->execute([$documento_id, $proyecto_id]);
    $documento = $stmt->fetch();
    
    if (!$documento) {
        throw new Exception('Documento no encontrado o no tienes permiso para eliminarlo');
    }
    
    // Ruta completa del archivo
    $ruta_archivo = '../' . $documento['ruta_archivo'];
    
    // Eliminar el archivo físico si existe
    if (file_exists($ruta_archivo)) {
        if (!unlink($ruta_archivo)) {
            // Si no se puede eliminar el archivo, continuar con la eliminación del registro
            // pero registrar el error
            error_log("No se pudo eliminar el archivo: " . $ruta_archivo);
        }
    }
    
    // Eliminar el registro de la base de datos (marcar como inactivo o eliminar físicamente)
    // Usaremos DELETE físico para eliminar completamente
    $stmt = $db->prepare("DELETE FROM documentos_proyecto_subidos WHERE id = ? AND proyecto_id = ?");
    $stmt->execute([$documento_id, $proyecto_id]);
    
    // Redirigir con mensaje de éxito
    header('Location: estudio.php?id=' . $proyecto_id . '&success=1&mensaje=' . urlencode('Documento eliminado exitosamente'));
    exit();
    
} catch (Exception $e) {
    // Redirigir con mensaje de error
    header('Location: estudio.php?id=' . $proyecto_id . '&error_doc=' . urlencode($e->getMessage()));
    exit();
}
?>

