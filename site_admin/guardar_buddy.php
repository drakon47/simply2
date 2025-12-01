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
            INSERT INTO usuarios (email, password, nombre, apellido, rol, idEntidad) 
            VALUES (?, ?, ?, ?, 'BUDDY', NULL)
        ");
        $stmt->execute([
            $_POST['email'],
            md5($_POST['password']),
            $_POST['nombre'],
            $_POST['apellido']
        ]);
        
        header('Location: buddys.php?success=1');
        exit();
        
    } catch (Exception $e) {
        header('Location: buddys.php?error=1');
        exit();
    }
} else {
    header('Location: buddys.php');
    exit();
}
?>
