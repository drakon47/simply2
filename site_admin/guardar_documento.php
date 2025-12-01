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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    try {
        $proyecto_id = $_POST['proyecto_id'] ?? null;
        $titulo = $_POST['titulo_documento'] ?? '';
        $fecha_vencimiento = !empty($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento'] : null;
        $descripcion = $_POST['descripcion_documento'] ?? null;
        
        // Validar que el proyecto existe
        if (!$proyecto_id) {
            throw new Exception('ID de proyecto no válido');
        }
        
        $stmt = $db->prepare("SELECT id FROM proyectos WHERE id = ? AND activo = 1");
        $stmt->execute([$proyecto_id]);
        if (!$stmt->fetch()) {
            throw new Exception('Proyecto no encontrado');
        }
        
        // Validar título
        if (empty($titulo)) {
            throw new Exception('El título del documento es requerido');
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
        
        // Obtener el siguiente ID de documento
        $stmt = $db->prepare("SELECT COALESCE(MAX(id), 0) + 1 as siguiente_id FROM documentos_proyecto WHERE proyecto_id = ?");
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
        
        // Generar nombre de archivo codificado: proyecto_id_documento_id.extension
        $nombre_codificado = $proyecto_id . '_' . $documento_id . '.' . $extension;
        $ruta_completa = $directorio_proyecto . $nombre_codificado;
        
        // Mover archivo al servidor
        if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            throw new Exception('Error al guardar el archivo en el servidor');
        }
        
        // Guardar información en la base de datos
        $ruta_relativa = 'documentos/proyecto_' . $proyecto_id . '/' . $nombre_codificado;
        
        $stmt = $db->prepare("
            INSERT INTO documentos_proyecto (proyecto_id, titulo, nombre_archivo, ruta_archivo, fecha_vencimiento, descripcion) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $proyecto_id,
            $titulo,
            $archivo['name'], // Nombre original
            $ruta_relativa,
            $fecha_vencimiento,
            $descripcion ?: null
        ]);
        
        // Redirigir con mensaje de éxito
        header('Location: centros_proyecto.php?id=' . $proyecto_id . '&success_documento=1');
        exit();
        
    } catch (Exception $e) {
        // Redirigir con mensaje de error
        $proyecto_id = $_POST['proyecto_id'] ?? null;
        if ($proyecto_id) {
            header('Location: centros_proyecto.php?id=' . $proyecto_id . '&error_documento=1');
        } else {
            header('Location: proyectos.php');
        }
        exit();
    }
} else {
    header('Location: proyectos.php');
    exit();
}
?>

