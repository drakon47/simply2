<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getUserInfo();

// Solo usuarios ADMIN pueden acceder
if ($user['rol'] !== 'ADMIN') {
    header('Location: ../index.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$evento_id = $_GET['id'] ?? null;
$proyecto_id = $_GET['proyecto_id'] ?? null;

if (!$evento_id || !$proyecto_id) {
    header('Location: proyectos.php');
    exit();
}

try {
    $stmt = $db->prepare("UPDATE eventos_tratamiento SET activo = 0 WHERE id = ? AND proyecto_id = ?");
    $stmt->execute([$evento_id, $proyecto_id]);
    
    header('Location: tratamiento.php?id=' . $proyecto_id . '&success=1');
    exit();
    
} catch (Exception $e) {
    header('Location: tratamiento.php?id=' . $proyecto_id . '&error=1');
    exit();
}
?>
