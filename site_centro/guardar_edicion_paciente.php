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

if ($_POST) {
    $paciente_id = $_POST['paciente_id'] ?? null;
    
    if (!$paciente_id) {
        header('Location: pacientes.php?error=1');
        exit();
    }
    
    try {
        // Verificar que el paciente pertenece al centro del usuario
        $stmt = $db->prepare("SELECT id FROM pacientes WHERE id = ? AND centro_id = ? AND activo = 1");
        $stmt->execute([$paciente_id, $user['identidad']]);
        if (!$stmt->fetch()) {
            header('Location: pacientes.php?error=4');
            exit();
        }
        
        // Validar formato de email si se proporciona
        if (!empty($_POST['email'])) {
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                header('Location: editar_paciente.php?id=' . $paciente_id . '&error=3');
                exit();
            }
        }
        
        // Validar que el proyecto pertenece al centro (si se seleccionó uno)
        if (!empty($_POST['proyecto_id'])) {
            $stmt = $db->prepare("
                SELECT COUNT(*) as count 
                FROM centros_proyectos 
                WHERE centro_id = ? AND proyecto_id = ? AND activo = 1
            ");
            $stmt->execute([$user['identidad'], $_POST['proyecto_id']]);
            $proyecto_valido = $stmt->fetch()['count'] > 0;
            
            if (!$proyecto_valido) {
                header('Location: editar_paciente.php?id=' . $paciente_id . '&error=2');
                exit();
            }
        }
        
        // Validar fecha de alta
        $fecha_alta = $_POST['fecha_alta'] ?: date('Y-m-d');
        if (!empty($_POST['fecha_alta'])) {
            $fecha_alta = $_POST['fecha_alta'];
        }
        
        // Actualizar el paciente
        $stmt = $db->prepare("
            UPDATE pacientes SET
                nombre = ?, apellido = ?, email = ?, telefono1 = ?, telefono2 = ?,
                domicilio_calle = ?, domicilio_numero = ?, domicilio_piso = ?, domicilio_depto = ?,
                domicilio_localidad = ?, domicilio_provincia = ?, familiar_contacto = ?, telefono_familiar = ?,
                consentimiento_firmado = ?, comentarios = ?, proyecto_id = ?, fecha_alta = ?
            WHERE id = ? AND centro_id = ? AND activo = 1
        ");
        
        $stmt->execute([
            $_POST['nombre'],
            $_POST['apellido'],
            $_POST['email'] ?: null,
            $_POST['telefono1'] ?: null,
            $_POST['telefono2'] ?: null,
            $_POST['domicilio_calle'] ?: null,
            $_POST['domicilio_numero'] ?: null,
            $_POST['domicilio_piso'] ?: null,
            $_POST['domicilio_depto'] ?: null,
            $_POST['domicilio_localidad'] ?: null,
            $_POST['domicilio_provincia'] ?: null,
            $_POST['familiar_contacto'] ?: null,
            $_POST['telefono_familiar'] ?: null,
            $_POST['consentimiento_firmado'],
            $_POST['comentarios'] ?: null,
            $_POST['proyecto_id'] ?: null,
            $fecha_alta,
            $paciente_id,
            $user['identidad']
        ]);
        
        header('Location: editar_paciente.php?id=' . $paciente_id . '&success=1');
        exit();
        
    } catch (Exception $e) {
        header('Location: editar_paciente.php?id=' . $paciente_id . '&error=1');
        exit();
    }
} else {
    header('Location: pacientes.php');
    exit();
}
?>
