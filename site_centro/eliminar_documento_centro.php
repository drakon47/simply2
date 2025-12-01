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
$centro_id = $user['identidad'];

if (!$documento_id) {
    header('Location: documentos_centro.php?error=' . urlencode('ID de documento no válido'));
    exit();
}

try {
    // Obtener información del documento para verificar que pertenece al centro
    $stmt = $db->prepare("
        SELECT * FROM documentos_centro_subidos 
        WHERE id = ? AND centro_id = ? AND activo = 1
    ");
    $stmt->execute([$documento_id, $centro_id]);
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
    $stmt = $db->prepare("DELETE FROM documentos_centro_subidos WHERE id = ? AND centro_id = ?");
    $stmt->execute([$documento_id, $centro_id]);
    
    // Redirigir con mensaje de éxito
    header('Location: documentos_centro.php?success=1&mensaje=' . urlencode('Documento eliminado exitosamente'));
    exit();
    
} catch (Exception $e) {
    // Redirigir con mensaje de error
    header('Location: documentos_centro.php?error=' . urlencode($e->getMessage()));
    exit();
}
?>

