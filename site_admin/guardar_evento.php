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
            INSERT INTO eventos_tratamiento (proyecto_id, titulo, descripcion, dias_desde_inicio, tipo_evento) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['proyecto_id'],
            $_POST['titulo'],
            $_POST['descripcion'] ?: null,
            $_POST['dias_desde_inicio'],
            $_POST['tipo_evento']
        ]);
        
        // Redirigir según el origen del formulario
        $redirect = $_POST['redirect'] ?? 'tratamiento.php';
        if ($redirect === 'centros_proyecto.php') {
            header('Location: centros_proyecto.php?id=' . $_POST['proyecto_id'] . '&success_evento=1');
        } else {
            header('Location: tratamiento.php?id=' . $_POST['proyecto_id'] . '&success=1');
        }
        exit();
        
    } catch (Exception $e) {
        // Redirigir según el origen del formulario en caso de error
        $redirect = $_POST['redirect'] ?? 'tratamiento.php';
        if ($redirect === 'centros_proyecto.php') {
            header('Location: centros_proyecto.php?id=' . $_POST['proyecto_id'] . '&error_evento=1');
        } else {
            header('Location: tratamiento.php?id=' . $_POST['proyecto_id'] . '&error=1');
        }
        exit();
    }
} else {
    header('Location: proyectos.php');
    exit();
}
?>
