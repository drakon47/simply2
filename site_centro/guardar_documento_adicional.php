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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    try {
        $centro_id = $_POST['centro_id'] ?? null;
        $descripcion = trim($_POST['descripcion'] ?? '');
        $fecha_vencimiento = !empty($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento'] : null;
        $fecha_alerta_roja = !empty($_POST['fecha_alerta_roja']) ? $_POST['fecha_alerta_roja'] : null;
        $fecha_alerta_amarilla = !empty($_POST['fecha_alerta_amarilla']) ? $_POST['fecha_alerta_amarilla'] : null;
        $responsable = !empty($_POST['responsable']) ? trim($_POST['responsable']) : null;
        $email = !empty($_POST['email']) ? trim($_POST['email']) : null;
        
        // Validar descripción
        if (empty($descripcion)) {
            throw new Exception('La descripción es requerida');
        }
        
        // Validar que el centro_id coincide con el usuario
        if (!$centro_id || $centro_id != $user['identidad']) {
            throw new Exception('Centro no válido');
        }
        
        // Validar archivo
        if ($_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error al subir el archivo');
        }
        
        // Validar tipo de archivo
        $archivo = $_FILES['archivo'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensiones_permitidas = ['png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg'];
        
        if (!in_array($extension, $extensiones_permitidas)) {
            throw new Exception('Tipo de archivo no permitido. Solo se permiten: PNG, PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG');
        }
        
        // Validar tamaño (máximo 10MB)
        $tamaño_maximo = 10 * 1024 * 1024; // 10MB
        if ($archivo['size'] > $tamaño_maximo) {
            throw new Exception('El archivo es demasiado grande. Tamaño máximo: 10MB');
        }
        
        // Validar formato de email si se proporciona
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El formato del email no es válido');
        }
        
        // Obtener el siguiente ID de documento
        $stmt = $db->prepare("SELECT COALESCE(MAX(id), 0) + 1 as siguiente_id FROM documentos_centro_adicional WHERE centro_id = ?");
        $stmt->execute([$centro_id]);
        $resultado = $stmt->fetch();
        $documento_id = $resultado['siguiente_id'];
        
        // Crear directorio si no existe
        $directorio_base = '../documentos/';
        $directorio_centro = $directorio_base . 'centro_' . $centro_id . '/';
        
        if (!file_exists($directorio_base)) {
            mkdir($directorio_base, 0755, true);
        }
        
        if (!file_exists($directorio_centro)) {
            mkdir($directorio_centro, 0755, true);
        }
        
        // Generar nombre de archivo codificado: centro_id_adicional_documento_id.extension
        $nombre_codificado = $centro_id . '_adicional_' . $documento_id . '.' . $extension;
        $ruta_completa = $directorio_centro . $nombre_codificado;
        
        // Mover archivo al servidor
        if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            throw new Exception('Error al guardar el archivo en el servidor');
        }
        
        // Guardar información en la base de datos
        $ruta_relativa = 'documentos/centro_' . $centro_id . '/' . $nombre_codificado;
        
        $stmt = $db->prepare("
            INSERT INTO documentos_centro_adicional (
                centro_id, 
                descripcion,
                nombre_archivo, 
                ruta_archivo, 
                fecha_vencimiento, 
                fecha_alerta_roja, 
                fecha_alerta_amarilla, 
                responsable, 
                email
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $centro_id,
            $descripcion,
            $archivo['name'], // Nombre original
            $ruta_relativa,
            $fecha_vencimiento ?: null,
            $fecha_alerta_roja ?: null,
            $fecha_alerta_amarilla ?: null,
            $responsable,
            $email
        ]);
        
        // Redirigir con mensaje de éxito
        header('Location: documentos_centro.php?success=1&mensaje=' . urlencode('Documento adicional subido exitosamente'));
        exit();
        
    } catch (Exception $e) {
        // Redirigir con mensaje de error
        header('Location: documentos_centro.php?error=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    header('Location: documentos_centro.php');
    exit();
}
?>

