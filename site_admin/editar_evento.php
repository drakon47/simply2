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

// Obtener información del evento
$stmt = $db->prepare("SELECT * FROM eventos_tratamiento WHERE id = ? AND proyecto_id = ? AND activo = 1");
$stmt->execute([$evento_id, $proyecto_id]);
$evento = $stmt->fetch();

if (!$evento) {
    header('Location: tratamiento.php?id=' . $proyecto_id);
    exit();
}

if ($_POST) {
    try {
        $stmt = $db->prepare("
            UPDATE eventos_tratamiento 
            SET titulo = ?, descripcion = ?, dias_desde_inicio = ?, tipo_evento = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $_POST['titulo'],
            $_POST['descripcion'] ?: null,
            $_POST['dias_desde_inicio'],
            $_POST['tipo_evento'],
            $evento_id
        ]);
        
        header('Location: tratamiento.php?id=' . $proyecto_id . '&success=1');
        exit();
        
    } catch (Exception $e) {
        header('Location: tratamiento.php?id=' . $proyecto_id . '&error=1');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Evento - Proyecto Simply</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header" style="background: #9C27B0; color: white;">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-edit me-2"></i>Editar Evento de Tratamiento
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="titulo" class="form-label">Título del Evento *</label>
                                        <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($evento['titulo']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="dias_desde_inicio" class="form-label">Días desde Inicio *</label>
                                        <input type="number" class="form-control" id="dias_desde_inicio" name="dias_desde_inicio" value="<?php echo $evento['dias_desde_inicio']; ?>" min="0" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="tipo_evento" class="form-label">Tipo de Evento *</label>
                                <select class="form-select" id="tipo_evento" name="tipo_evento" required>
                                    <option value="Presencial" <?php echo $evento['tipo_evento'] == 'Presencial' ? 'selected' : ''; ?>>Presencial</option>
                                    <option value="Virtual" <?php echo $evento['tipo_evento'] == 'Virtual' ? 'selected' : ''; ?>>Virtual</option>
                                    <option value="Llamado" <?php echo $evento['tipo_evento'] == 'Llamado' ? 'selected' : ''; ?>>Llamado</option>
                                    <option value="Otro" <?php echo $evento['tipo_evento'] == 'Otro' ? 'selected' : ''; ?>>Otro</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($evento['descripcion']); ?></textarea>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="tratamiento.php?id=<?php echo $proyecto_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn" style="background: #9C27B0; color: white; border: none;">
                                    <i class="fas fa-save me-1"></i>Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
