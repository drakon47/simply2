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

if ($_POST) {
    try {
        $stmt = $db->prepare("
            INSERT INTO proyectos (nombre, patrocinante_id, descripcion) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $_POST['nombre'],
            $_POST['patrocinante_id'],
            $_POST['descripcion']
        ]);
        
        header('Location: proyectos.php?success=1');
        exit();
        
    } catch (Exception $e) {
        header('Location: proyectos.php?error=1');
        exit();
    }
} else {
    header('Location: proyectos.php');
    exit();
}
?>
