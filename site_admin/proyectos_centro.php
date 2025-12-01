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

// Obtener parámetros
$centro_id = $_GET['centro_id'] ?? null;
$centro_nombre = $_GET['centro_nombre'] ?? '';

if (!$centro_id) {
    header('Location: centros.php');
    exit();
}

// Obtener información del centro
$stmt = $db->prepare("SELECT * FROM centros WHERE id = ? AND activo = 1");
$stmt->execute([$centro_id]);
$centro = $stmt->fetch();

if (!$centro) {
    header('Location: centros.php');
    exit();
}

// Obtener proyectos asociados al centro
$stmt = $db->prepare("
    SELECT p.*, cp.fecha_asociacion 
    FROM proyectos p 
    INNER JOIN centros_proyectos cp ON p.id = cp.proyecto_id 
    WHERE cp.centro_id = ? AND cp.activo = 1 AND p.activo = 1
    ORDER BY p.nombre
");
$stmt->execute([$centro_id]);
$proyectos_asociados = $stmt->fetchAll();

// Obtener todos los proyectos disponibles (no asociados)
$stmt = $db->prepare("
    SELECT p.* 
    FROM proyectos p 
    WHERE p.activo = 1 
    AND p.id NOT IN (
        SELECT proyecto_id 
        FROM centros_proyectos 
        WHERE centro_id = ? AND activo = 1
    )
    ORDER BY p.nombre
");
$stmt->execute([$centro_id]);
$proyectos_disponibles = $stmt->fetchAll();
?>

<?php
$pageTitle = 'Proyectos del Centro: ' . $centro['nombre'];
$rolColor = '#9C27B0'; // Púrpura para ADMIN
include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-project-diagram me-2"></i>Proyectos del Centro: <?php echo htmlspecialchars($centro['nombre']); ?>
            </h2>
            <div>
                <a href="ver_centro.php?id=<?php echo $centro_id; ?>" class="btn btn-sm me-2" style="background: #9C27B0; color: white; border: none;" title="Ver Ficha del Centro">
                    <i class="fas fa-eye me-1"></i>Ver Ficha del Centro
                </a>
                <a href="centros.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver a Centros
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Proyectos Asociados -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #28a745; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-check-circle me-2"></i>Proyectos Asociados (<?php echo count($proyectos_asociados); ?>)
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($proyectos_asociados)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                        <p>No hay proyectos asociados a este centro.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Proyecto</th>
                                    <th>Patrocinante</th>
                                    <th>Descripción</th>
                                    <th>Fecha de Asociación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proyectos_asociados as $proyecto): ?>
                                <?php
                                // Obtener nombre del patrocinante
                                $stmt = $db->prepare("SELECT nombre FROM laboratorios WHERE id = ?");
                                $stmt->execute([$proyecto['patrocinante_id']]);
                                $patrocinante = $stmt->fetch();
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($proyecto['nombre']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($patrocinante['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($proyecto['descripcion']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($proyecto['fecha_asociacion'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger" onclick="desasociarProyecto(<?php echo $proyecto['id']; ?>, '<?php echo htmlspecialchars($proyecto['nombre']); ?>')">
                                            <i class="fas fa-times"></i> Desasociar
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Proyectos Disponibles -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #17a2b8; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-plus-circle me-2"></i>Proyectos Disponibles (<?php echo count($proyectos_disponibles); ?>)
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($proyectos_disponibles)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <p>Todos los proyectos están asociados a este centro.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Proyecto</th>
                                    <th>Patrocinante</th>
                                    <th>Descripción</th>
                                    <th>Fecha de Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proyectos_disponibles as $proyecto): ?>
                                <?php
                                // Obtener nombre del patrocinante
                                $stmt = $db->prepare("SELECT nombre FROM laboratorios WHERE id = ?");
                                $stmt->execute([$proyecto['patrocinante_id']]);
                                $patrocinante = $stmt->fetch();
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($proyecto['nombre']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($patrocinante['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($proyecto['descripcion']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($proyecto['fecha_alta'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm" style="background: #28a745; color: white; border: none;" onclick="asociarProyecto(<?php echo $proyecto['id']; ?>, '<?php echo htmlspecialchars($proyecto['nombre']); ?>')">
                                            <i class="fas fa-plus"></i> Asociar
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function asociarProyecto(proyectoId, proyectoNombre) {
    if (confirm('¿Deseas asociar el proyecto "' + proyectoNombre + '" a este centro?')) {
        fetch('gestionar_proyectos_centro.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'accion=asociar&centro_id=<?php echo $centro_id; ?>&proyecto_id=' + proyectoId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error al procesar la solicitud');
        });
    }
}

function desasociarProyecto(proyectoId, proyectoNombre) {
    if (confirm('¿Deseas desasociar el proyecto "' + proyectoNombre + '" de este centro?')) {
        fetch('gestionar_proyectos_centro.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'accion=desasociar&centro_id=<?php echo $centro_id; ?>&proyecto_id=' + proyectoId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error al procesar la solicitud');
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>
