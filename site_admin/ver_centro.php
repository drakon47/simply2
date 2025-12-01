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

// Obtener ID del centro
$centro_id = $_GET['id'] ?? null;

if (!$centro_id) {
    header('Location: centros.php');
    exit();
}

// Obtener información del centro
$stmt = $db->prepare("SELECT * FROM centros WHERE id = ? AND activo = 1");
$stmt->execute([$centro_id]);
$centro = $stmt->fetch();

if (!$centro) {
    header('Location: centros.php?error=centro_no_encontrado');
    exit();
}

// Contar pacientes del centro
$stmt = $db->prepare("SELECT COUNT(*) as total FROM pacientes WHERE centro_id = ? AND activo = 1");
$stmt->execute([$centro_id]);
$totalPacientes = $stmt->fetch()['total'];

// Contar proyectos del centro
$stmt = $db->prepare("SELECT COUNT(*) as total FROM centros_proyectos WHERE centro_id = ? AND activo = 1");
$stmt->execute([$centro_id]);
$totalProyectos = $stmt->fetch()['total'];

// Obtener proyectos asociados al centro
$stmt = $db->prepare("
    SELECT p.*, cp.fecha_asociacion 
    FROM proyectos p 
    INNER JOIN centros_proyectos cp ON p.id = cp.proyecto_id 
    WHERE cp.centro_id = ? AND cp.activo = 1 AND p.activo = 1
    ORDER BY p.nombre
");
$stmt->execute([$centro_id]);
$proyectos = $stmt->fetchAll();

// Obtener pacientes del centro (con información del proyecto)
$stmt = $db->prepare("
    SELECT p.*, pr.nombre as proyecto_nombre
    FROM pacientes p
    LEFT JOIN proyectos pr ON p.proyecto_id = pr.id
    WHERE p.centro_id = ? AND p.activo = 1
    ORDER BY p.fecha_alta DESC
    LIMIT 10
");
$stmt->execute([$centro_id]);
$pacientes = $stmt->fetchAll();
?>

<?php
$pageTitle = 'Ficha del Centro: ' . $centro['nombre'];
$rolColor = '#9C27B0'; // Púrpura para ADMIN
include '../includes/header.php';
?>

<!-- Mensajes de error -->
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>Centro no encontrado.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-hospital me-2"></i>Ficha del Centro: <?php echo htmlspecialchars($centro['nombre']); ?>
            </h2>
            <a href="centros.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver a Centros
            </a>
        </div>
    </div>
</div>

<!-- Información General del Centro -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: #9C27B0; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Información General
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Nombre:</strong></div>
                    <div class="col-sm-8"><?php echo htmlspecialchars($centro['nombre']); ?></div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Dirección:</strong></div>
                    <div class="col-sm-8"><?php echo htmlspecialchars($centro['direccion']); ?></div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Localidad:</strong></div>
                    <div class="col-sm-8"><?php echo htmlspecialchars($centro['localidad']); ?></div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Provincia:</strong></div>
                    <div class="col-sm-8"><?php echo htmlspecialchars($centro['provincia']); ?></div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>País:</strong></div>
                    <div class="col-sm-8"><?php echo htmlspecialchars($centro['pais']); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: #9C27B0; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-envelope me-2"></i>Información de Contacto
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Email Referencia:</strong></div>
                    <div class="col-sm-8">
                        <?php if ($centro['email_referencia']): ?>
                            <a href="mailto:<?php echo htmlspecialchars($centro['email_referencia']); ?>">
                                <?php echo htmlspecialchars($centro['email_referencia']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">No especificado</span>
                        <?php endif; ?>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Email Referencia 2:</strong></div>
                    <div class="col-sm-8">
                        <?php if ($centro['email_referencia_2']): ?>
                            <a href="mailto:<?php echo htmlspecialchars($centro['email_referencia_2']); ?>">
                                <?php echo htmlspecialchars($centro['email_referencia_2']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">No especificado</span>
                        <?php endif; ?>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Teléfono:</strong></div>
                    <div class="col-sm-8">
                        <?php if ($centro['telefono']): ?>
                            <a href="tel:<?php echo htmlspecialchars($centro['telefono']); ?>">
                                <?php echo htmlspecialchars($centro['telefono']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">No especificado</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-primary"><?php echo $totalPacientes; ?></h3>
                <p class="card-text">
                    <i class="fas fa-user-injured me-1"></i>Pacientes
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success"><?php echo $totalProyectos; ?></h3>
                <p class="card-text">
                    <i class="fas fa-project-diagram me-1"></i>Proyectos
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-info"><?php echo date('d/m/Y', strtotime($centro['fecha_alta'])); ?></h3>
                <p class="card-text">
                    <i class="fas fa-calendar me-1"></i>Fecha de Alta
                </p>
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
                    <i class="fas fa-project-diagram me-2"></i>Proyectos Asociados (<?php echo count($proyectos); ?>)
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($proyectos)): ?>
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proyectos as $proyecto): ?>
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

<!-- Pacientes Recientes -->
<?php if (!empty($pacientes)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #4FC3F7; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-user-injured me-2"></i>Pacientes Recientes (Últimos 10)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Email</th>
                                <th>Proyecto</th>
                                <th>Fecha de Alta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pacientes as $paciente): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($paciente['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($paciente['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($paciente['email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($paciente['proyecto_nombre'] ?? 'Sin proyecto'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($paciente['fecha_alta'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Información del Sistema -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #6c757d; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Información del Sistema
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>ID del Centro:</strong><br>
                        <span class="text-muted"><?php echo $centro['id']; ?></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Fecha de Registro:</strong><br>
                        <span class="text-muted"><?php echo date('d/m/Y H:i', strtotime($centro['fecha_alta'])); ?></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Estado:</strong><br>
                        <span class="badge bg-success">Activo</span>
                    </div>
                    <div class="col-md-3">
                        <a href="proyectos_centro.php?centro_id=<?php echo $centro['id']; ?>&centro_nombre=<?php echo urlencode($centro['nombre']); ?>" class="btn btn-sm" style="background: #28a745; color: white; border: none;">
                            <i class="fas fa-project-diagram me-1"></i>Gestionar Proyectos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

