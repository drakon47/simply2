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
            INSERT INTO centros (nombre, direccion, localidad, provincia, pais, email_referencia, email_referencia_2, telefono) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['nombre'],
            $_POST['direccion'],
            $_POST['localidad'],
            $_POST['provincia'],
            $_POST['pais'],
            $_POST['email_referencia'] ?: null,
            $_POST['email_referencia_2'] ?: null,
            $_POST['telefono'] ?: null
        ]);
        
        header('Location: centros.php?success=1');
        exit();
        
    } catch (Exception $e) {
        header('Location: centros.php?error=1');
        exit();
    }
} else {
    header('Location: centros.php');
    exit();
}
?>
