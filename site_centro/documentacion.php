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

// Obtener parámetros
$proyecto_id = $_GET['id'] ?? null;
$centro_id = $user['identidad'];

if (!$proyecto_id) {
    header('Location: index.php');
    exit();
}

// Verificar que el proyecto está asociado al centro
$stmt = $db->prepare("
    SELECT p.*, l.nombre as laboratorio_nombre, l.pais as laboratorio_pais
    FROM proyectos p 
    JOIN laboratorios l ON p.patrocinante_id = l.id 
    INNER JOIN centros_proyectos cp ON p.id = cp.proyecto_id
    WHERE p.id = ? AND cp.centro_id = ? AND p.activo = 1 AND cp.activo = 1
");
$stmt->execute([$proyecto_id, $centro_id]);
$proyecto = $stmt->fetch();

if (!$proyecto) {
    header('Location: index.php?error=proyecto_no_encontrado');
    exit();
}

// Configurar título de la página
$pageTitle = 'Información del Estudio: ' . htmlspecialchars($proyecto['nombre']);
$rolColor = '#4FC3F7'; // Azul para CENTRO
include '../includes/header.php';

// Contar cantidad de pacientes en el proyecto para este centro
$stmt = $db->prepare("SELECT COUNT(*) as total FROM pacientes WHERE proyecto_id = ? AND centro_id = ? AND activo = 1");
$stmt->execute([$proyecto_id, $centro_id]);
$totalPacientes = $stmt->fetch()['total'];

// Contar pacientes con consentimiento firmado
$stmt = $db->prepare("SELECT COUNT(*) as total FROM pacientes WHERE proyecto_id = ? AND centro_id = ? AND consentimiento_firmado = 'SI' AND activo = 1");
$stmt->execute([$proyecto_id, $centro_id]);
$pacientesConConsentimiento = $stmt->fetch()['total'];

// Obtener lista de pacientes del proyecto en este centro
$stmt = $db->prepare("
    SELECT id, nombre, apellido, email, telefono1, consentimiento_firmado, fecha_alta
    FROM pacientes 
    WHERE proyecto_id = ? AND centro_id = ? AND activo = 1 
    ORDER BY apellido, nombre
");
$stmt->execute([$proyecto_id, $centro_id]);
$pacientes = $stmt->fetchAll();

// Obtener eventos de tratamiento del proyecto
$eventos = [];
try {
    $stmt = $db->prepare("
        SELECT * FROM eventos_tratamiento 
        WHERE proyecto_id = ? AND activo = 1 
        ORDER BY dias_desde_inicio ASC
    ");
    $stmt->execute([$proyecto_id]);
    $eventos = $stmt->fetchAll();
} catch (PDOException $e) {
    // Si la tabla no existe, usar array vacío
    $eventos = [];
}

// Obtener documentos del proyecto
$documentos = [];
try {
    $stmt = $db->prepare("
        SELECT * FROM documentos_proyecto 
        WHERE proyecto_id = ? AND activo = 1 
        ORDER BY fecha_subida DESC
    ");
    $stmt->execute([$proyecto_id]);
    $documentos = $stmt->fetchAll();
} catch (PDOException $e) {
    // Si la tabla no existe, usar array vacío
    $documentos = [];
}
?>

<!-- Mensajes de error -->
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>Proyecto no encontrado o no tiene acceso.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>


<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-project-diagram me-2"></i>Información del Estudio: <?php echo htmlspecialchars($proyecto['nombre']); ?>
            </h2>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver al Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Información General del Estudio -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #4FC3F7; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Información General del Estudio
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-hashtag me-1"></i>ID del Proyecto:</strong><br>
                        <span class="badge bg-secondary mt-1" style="font-size: 1em; padding: 0.5em;">#<?php echo $proyecto['id']; ?></span>
                    </div>
                    <div class="col-md-4">
                        <strong><i class="fas fa-check-circle me-1"></i>Estado:</strong><br>
                        <?php if ($proyecto['activo']): ?>
                            <span class="badge bg-success mt-1" style="font-size: 1em; padding: 0.5em;">
                                <i class="fas fa-check me-1"></i>Activo
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger mt-1" style="font-size: 1em; padding: 0.5em;">
                                <i class="fas fa-times me-1"></i>Inactivo
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <strong><i class="fas fa-calendar-alt me-1"></i>Fecha de Alta:</strong><br>
                        <p class="mb-0 mt-1">
                            <i class="fas fa-calendar me-1"></i><?php echo date('d/m/Y', strtotime($proyecto['fecha_alta'])); ?><br>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i><?php echo date('H:i:s', strtotime($proyecto['fecha_alta'])); ?>
                            </small>
                        </p>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-12">
                        <strong><i class="fas fa-tag me-1"></i>Nombre del Estudio/Proyecto:</strong><br>
                        <h5 class="mt-2 mb-0" style="color: #4FC3F7;">
                            <?php echo htmlspecialchars($proyecto['nombre']); ?>
                        </h5>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong><i class="fas fa-flask me-1"></i>Patrocinante (Laboratorio):</strong><br>
                        <div class="mt-2">
                            <span class="badge" style="background: #4FC3F7; color: white; font-size: 1em; padding: 0.6em;">
                                <i class="fas fa-building me-1"></i><?php echo htmlspecialchars($proyecto['laboratorio_nombre']); ?>
                            </span>
                            <br>
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-hashtag me-1"></i>ID Patrocinante: <?php echo $proyecto['patrocinante_id']; ?>
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-globe me-1"></i>País del Patrocinante:</strong><br>
                        <p class="mb-0 mt-2">
                            <i class="fas fa-flag me-1"></i><?php echo htmlspecialchars($proyecto['laboratorio_pais']); ?>
                        </p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <strong><i class="fas fa-align-left me-1"></i>Descripción del Estudio:</strong><br>
                        <div class="mt-2 p-3" style="background: #f8f9fa; border-radius: 5px; border-left: 4px solid #4FC3F7;">
                            <p class="mb-0" style="text-align: justify; line-height: 1.8;">
                                <?php if ($proyecto['descripcion']): ?>
                                    <?php echo nl2br(htmlspecialchars($proyecto['descripcion'])); ?>
                                <?php else: ?>
                                    <span class="text-muted"><i>No hay descripción disponible para este estudio.</i></span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas del Estudio -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center" style="background: linear-gradient(135deg, #4FC3F7 0%, #29B6F6 100%); color: white;">
            <div class="card-body">
                <h3 class="card-title"><?php echo $totalPacientes; ?></h3>
                <p class="card-text mb-0">
                    <i class="fas fa-user-injured me-1"></i>Total de Pacientes
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center" style="background: linear-gradient(135deg, #66BB6A 0%, #4CAF50 100%); color: white;">
            <div class="card-body">
                <h3 class="card-title"><?php echo $pacientesConConsentimiento; ?></h3>
                <p class="card-text mb-0">
                    <i class="fas fa-file-signature me-1"></i>Con Consentimiento
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center" style="background: linear-gradient(135deg, #FFA726 0%, #FF9800 100%); color: white;">
            <div class="card-body">
                <h3 class="card-title"><?php echo $totalPacientes - $pacientesConConsentimiento; ?></h3>
                <p class="card-text mb-0">
                    <i class="fas fa-exclamation-circle me-1"></i>Sin Consentimiento
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Pacientes -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: #66BB6A; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>Pacientes del Estudio (<?php echo count($pacientes); ?>)
                </h5>
                <a href="pacientes.php?proyecto=<?php echo $proyecto_id; ?>" class="btn btn-sm" style="background: white; color: #66BB6A; border: none;">
                    <i class="fas fa-eye me-1"></i>Ver Todos
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($pacientes)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-user-injured fa-3x mb-3"></i>
                        <p>No hay pacientes asociados a este estudio en su centro.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre Completo</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Consentimiento</th>
                                    <th>Fecha de Alta</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pacientes as $paciente): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($paciente['email']): ?>
                                            <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($paciente['email']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">No disponible</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($paciente['telefono1']): ?>
                                            <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($paciente['telefono1']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">No disponible</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($paciente['consentimiento_firmado'] == 'SI'): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Firmado
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-times me-1"></i>Pendiente
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($paciente['fecha_alta'])); ?></td>
                                    <td>
                                        <a href="pacientes.php?proyecto=<?php echo $proyecto_id; ?>&paciente=<?php echo $paciente['id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
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

<!-- Eventos de Tratamiento -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #17a2b8; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-medical me-2"></i>Eventos de Tratamiento (<?php echo count($eventos); ?>)
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($eventos)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                        <p>No hay eventos de tratamiento asociados a este proyecto.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Días desde Inicio</th>
                                    <th>Tipo de Evento</th>
                                    <th>Descripción</th>
                                    <th>Fecha de Creación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($eventos as $evento): ?>
                                <tr>
                                    <td><?php echo $evento['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($evento['titulo']); ?></strong></td>
                                    <td>
                                        <span class="badge bg-primary">Día <?php echo $evento['dias_desde_inicio']; ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeColors = [
                                            'Presencial' => 'bg-success',
                                            'Virtual' => 'bg-info',
                                            'Llamado' => 'bg-warning',
                                            'Otro' => 'bg-secondary'
                                        ];
                                        $color = $badgeColors[$evento['tipo_evento']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $color; ?>"><?php echo htmlspecialchars($evento['tipo_evento']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($evento['descripcion']): ?>
                                            <?php echo htmlspecialchars($evento['descripcion']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Sin descripción</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($evento['fecha_creacion'])); ?></td>
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

<!-- Documentación -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #FF9800; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>Documentación (<?php echo count($documentos); ?>)
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($documentos)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                        <p>No hay documentos asociados a este proyecto.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Archivo</th>
                                    <th>Fecha Vencimiento</th>
                                    <th>Descripción</th>
                                    <th>Fecha Subida</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documentos as $documento): ?>
                                <tr>
                                    <td><?php echo $documento['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($documento['titulo']); ?></strong></td>
                                    <td>
                                        <a href="../<?php echo htmlspecialchars($documento['ruta_archivo']); ?>" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-file-download me-1"></i><?php echo htmlspecialchars($documento['nombre_archivo']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($documento['fecha_vencimiento']): ?>
                                            <?php 
                                            $fecha_vencimiento = strtotime($documento['fecha_vencimiento']);
                                            $hoy = strtotime('today');
                                            $dias_restantes = ($fecha_vencimiento - $hoy) / 86400;
                                            
                                            if ($dias_restantes < 0) {
                                                $badge_class = 'bg-danger';
                                                $texto = 'Vencido';
                                            } elseif ($dias_restantes <= 30) {
                                                $badge_class = 'bg-warning';
                                                $texto = 'Por vencer';
                                            } else {
                                                $badge_class = 'bg-success';
                                                $texto = 'Vigente';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <?php echo date('d/m/Y', strtotime($documento['fecha_vencimiento'])); ?> 
                                                <small>(<?php echo $texto; ?>)</small>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Sin fecha</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($documento['descripcion']): ?>
                                            <?php echo htmlspecialchars(substr($documento['descripcion'], 0, 50)) . (strlen($documento['descripcion']) > 50 ? '...' : ''); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Sin descripción</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($documento['fecha_subida'])); ?></td>
                                    <td>
                                        <a href="../<?php echo htmlspecialchars($documento['ruta_archivo']); ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Descargar">
                                            <i class="fas fa-download"></i>
                                        </a>
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

<?php include '../includes/footer.php'; ?>

