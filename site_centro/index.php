<?php
$pageTitle = 'Centro - Dashboard';
$rolColor = '#4FC3F7'; // Azul de la imagen
include '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Obtener estadísticas para el centro
$user = $auth->getUserInfo();
$centro_id = $user['identidad'];

// Estadísticas de pacientes del centro
$stmt = $db->prepare("SELECT COUNT(*) as total_pacientes FROM pacientes WHERE centro_id = ? AND activo = 1");
$stmt->execute([$centro_id]);
$totalPacientes = $stmt->fetch()['total_pacientes'];

$stmt = $db->prepare("SELECT COUNT(*) as pacientes_consentimiento FROM pacientes WHERE centro_id = ? AND consentimiento_firmado = 'SI' AND activo = 1");
$stmt->execute([$centro_id]);
$pacientesConsentimiento = $stmt->fetch()['pacientes_consentimiento'];

// Obtener proyectos asociados al centro
$stmt = $db->prepare("
    SELECT COUNT(*) as total_proyectos 
    FROM proyectos p 
    INNER JOIN centros_proyectos cp ON p.id = cp.proyecto_id 
    WHERE cp.centro_id = ? AND cp.activo = 1 AND p.activo = 1
");
$stmt->execute([$centro_id]);
$totalProyectos = $stmt->fetch()['total_proyectos'];

// Obtener laboratorios que patrocinan los proyectos del centro
$stmt = $db->prepare("
    SELECT COUNT(DISTINCT l.id) as total_laboratorios 
    FROM laboratorios l 
    INNER JOIN proyectos p ON l.id = p.patrocinante_id 
    INNER JOIN centros_proyectos cp ON p.id = cp.proyecto_id 
    WHERE cp.centro_id = ? AND cp.activo = 1 AND p.activo = 1 AND l.activo = 1
");
$stmt->execute([$centro_id]);
$totalLaboratorios = $stmt->fetch()['total_laboratorios'];

// Obtener información detallada de los estudios/proyectos del centro
$stmt = $db->prepare("
    SELECT p.id, p.nombre as proyecto_nombre, p.descripcion as proyecto_descripcion,
           l.nombre as laboratorio_nombre, l.pais as laboratorio_pais,
           COUNT(pa.id) as cantidad_pacientes
    FROM proyectos p 
    INNER JOIN centros_proyectos cp ON p.id = cp.proyecto_id 
    LEFT JOIN laboratorios l ON p.patrocinante_id = l.id
    LEFT JOIN pacientes pa ON p.id = pa.proyecto_id AND pa.centro_id = ? AND pa.activo = 1
    WHERE cp.centro_id = ? AND cp.activo = 1 AND p.activo = 1
    GROUP BY p.id, p.nombre, p.descripcion, l.nombre, l.pais
    ORDER BY p.nombre
");
$stmt->execute([$centro_id, $centro_id]);
$estudios = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">
            <i class="fas fa-hospital me-2"></i>Panel de Centro
        </h2>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card" style="background: linear-gradient(135deg, #4FC3F7 0%, #29B6F6 100%); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $totalPacientes; ?></h4>
                        <p class="card-text">Mis Pacientes</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-injured fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card" style="background: linear-gradient(135deg, #4FC3F7 0%, #29B6F6 100%); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $pacientesConsentimiento; ?></h4>
                        <p class="card-text">Con Consentimiento</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-file-signature fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card" style="background: linear-gradient(135deg, #4FC3F7 0%, #29B6F6 100%); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $totalProyectos; ?></h4>
                        <p class="card-text">Proyectos Activos</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-project-diagram fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card" style="background: linear-gradient(135deg, #4FC3F7 0%, #29B6F6 100%); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $totalLaboratorios; ?></h4>
                        <p class="card-text">Patrocinantes</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-flask fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tarjetas de Estudios/Proyectos del Centro -->
<?php if (!empty($estudios)): ?>
<div class="row mb-4">
    <div class="col-12">
        <h3 class="mb-3">
            <i class="fas fa-flask me-2"></i>Estudios Asociados al Centro
        </h3>
    </div>
</div>

<div class="row mb-4">
    <?php foreach ($estudios as $estudio): ?>
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-header" style="background: #4FC3F7; color: white;">
                <h6 class="card-title mb-0">
                    <i class="fas fa-project-diagram me-2"></i><?php echo htmlspecialchars($estudio['proyecto_nombre']); ?>
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Patrocinante:</strong><br>
                    <span class="text-muted"><?php echo htmlspecialchars($estudio['laboratorio_nombre']); ?></span>
                    <?php if ($estudio['laboratorio_pais']): ?>
                        <small class="text-muted">(<?php echo htmlspecialchars($estudio['laboratorio_pais']); ?>)</small>
                    <?php endif; ?>
                </div>
                
                <?php if ($estudio['proyecto_descripcion']): ?>
                <div class="mb-2">
                    <strong>Descripción:</strong><br>
                    <small class="text-muted"><?php echo htmlspecialchars(substr($estudio['proyecto_descripcion'], 0, 100)); ?><?php echo strlen($estudio['proyecto_descripcion']) > 100 ? '...' : ''; ?></small>
                </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge" style="background: #4FC3F7; color: white; font-size: 0.9em;">
                            <i class="fas fa-user-injured me-1"></i><?php echo $estudio['cantidad_pacientes']; ?> pacientes
                        </span>
                    </div>
                    <div>
                        <a href="estudio.php?id=<?php echo $estudio['id']; ?>" class="btn btn-sm btn-outline-info me-2">
                            <i class="fas fa-project-diagram me-1"></i>Estudio
                        </a>
                        <a href="pacientes.php?proyecto=<?php echo $estudio['id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>Ver Pacientes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No hay estudios asociados a este centro. Contacte al administrador para asociar proyectos.
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Gestión de Pacientes -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #4FC3F7; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-injured me-2"></i>Gestión de Pacientes
                </h5>
            </div>
            <div class="card-body">
                <p class="card-text">Administra los pacientes del centro médico.</p>
                <a href="pacientes.php" class="btn" style="background: #4FC3F7; color: white; border: none;">
                    <i class="fas fa-users me-1"></i>Ver Pacientes
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Documentación del Centro -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #4FC3F7; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-alt me-2"></i>Documentación del Centro
                </h5>
            </div>
            <div class="card-body">
                <p class="card-text">Consulta la lista de documentos requeridos para el centro.</p>
                <a href="documentos_centro.php" class="btn" style="background: #4FC3F7; color: white; border: none;">
                    <i class="fas fa-file-alt me-1"></i>Ver Documentación
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
