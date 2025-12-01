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
        $docu_requerida_proyecto_id = $_POST['docu_requerida_proyecto_id'] ?? null;
        $proyecto_id = $_POST['proyecto_id'] ?? null;
        $centro_id = $user['identidad'];
        $fecha_vencimiento = !empty($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento'] : null;
        $fecha_alerta_roja = !empty($_POST['fecha_alerta_roja']) ? $_POST['fecha_alerta_roja'] : null;
        $fecha_alerta_amarilla = !empty($_POST['fecha_alerta_amarilla']) ? $_POST['fecha_alerta_amarilla'] : null;
        $responsable = !empty($_POST['responsable']) ? trim($_POST['responsable']) : null;
        $email = !empty($_POST['email']) ? trim($_POST['email']) : null;
        
        // Validar que el documento requerido existe
        if (!$docu_requerida_proyecto_id) {
            throw new Exception('ID de documento requerido no válido');
        }
        
        $stmt = $db->prepare("SELECT id FROM docu_requerida_proyecto WHERE id = ? AND activo = 1");
        $stmt->execute([$docu_requerida_proyecto_id]);
        if (!$stmt->fetch()) {
            throw new Exception('Documento requerido no encontrado');
        }
        
        // Validar que el proyecto está asociado al centro
        if (!$proyecto_id) {
            throw new Exception('ID de proyecto no válido');
        }
        
        $stmt = $db->prepare("
            SELECT p.id 
            FROM proyectos p 
            INNER JOIN centros_proyectos cp ON p.id = cp.proyecto_id
            WHERE p.id = ? AND cp.centro_id = ? AND p.activo = 1 AND cp.activo = 1
        ");
        $stmt->execute([$proyecto_id, $centro_id]);
        if (!$stmt->fetch()) {
            throw new Exception('Proyecto no encontrado o no tiene acceso');
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
        $stmt = $db->prepare("SELECT COALESCE(MAX(id), 0) + 1 as siguiente_id FROM documentos_proyecto_subidos WHERE proyecto_id = ?");
        $stmt->execute([$proyecto_id]);
        $resultado = $stmt->fetch();
        $documento_id = $resultado['siguiente_id'];
        
        // Crear directorio si no existe
        $directorio_base = '../documentos/';
        $directorio_proyecto = $directorio_base . 'proyecto_' . $proyecto_id . '/';
        
        if (!file_exists($directorio_base)) {
            mkdir($directorio_base, 0755, true);
        }
        
        if (!file_exists($directorio_proyecto)) {
            mkdir($directorio_proyecto, 0755, true);
        }
        
        // Generar nombre de archivo codificado: proyecto_id_docu_id_documento_id.extension
        $nombre_codificado = $proyecto_id . '_' . $docu_requerida_proyecto_id . '_' . $documento_id . '.' . $extension;
        $ruta_completa = $directorio_proyecto . $nombre_codificado;
        
        // Mover archivo al servidor
        if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            throw new Exception('Error al guardar el archivo en el servidor');
        }
        
        // Guardar información en la base de datos
        $ruta_relativa = 'documentos/proyecto_' . $proyecto_id . '/' . $nombre_codificado;
        
        $stmt = $db->prepare("
            INSERT INTO documentos_proyecto_subidos (
                docu_requerida_proyecto_id, 
                proyecto_id, 
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
            $docu_requerida_proyecto_id,
            $proyecto_id,
            $archivo['name'], // Nombre original
            $ruta_relativa,
            $fecha_vencimiento ?: null,
            $fecha_alerta_roja ?: null,
            $fecha_alerta_amarilla ?: null,
            $responsable,
            $email
        ]);
        
        // Redirigir con mensaje de éxito
        header('Location: estudio.php?id=' . $proyecto_id . '&success=1');
        exit();
        
    } catch (Exception $e) {
        // Redirigir con mensaje de error
        header('Location: estudio.php?id=' . ($proyecto_id ?? '') . '&error_doc=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>

