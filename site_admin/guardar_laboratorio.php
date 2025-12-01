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
        $stmt = $db->prepare("INSERT INTO laboratorios (nombre, pais) VALUES (?, ?)");
        $stmt->execute([$_POST['nombre'], $_POST['pais']]);
        
        header('Location: laboratorios.php?success=1');
        exit();
        
    } catch (Exception $e) {
        header('Location: laboratorios.php?error=1');
        exit();
    }
} else {
    header('Location: laboratorios.php');
    exit();
}
?>
