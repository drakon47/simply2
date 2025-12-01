<?php
$pageTitle = 'Panel de Administración';
$rolColor = '#9C27B0'; // Púrpura para ADMIN
include '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Obtener estadísticas generales del sistema
$stmt = $db->query("SELECT COUNT(*) as total_usuarios FROM usuarios WHERE activo = 1");
$totalUsuarios = $stmt->fetch()['total_usuarios'];

$stmt = $db->query("SELECT COUNT(*) as total_centros FROM centros WHERE activo = 1");
$totalCentros = $stmt->fetch()['total_centros'];

$stmt = $db->query("SELECT COUNT(*) as total_laboratorios FROM laboratorios WHERE activo = 1");
$totalLaboratorios = $stmt->fetch()['total_laboratorios'];

$stmt = $db->query("SELECT COUNT(*) as total_proyectos FROM proyectos WHERE activo = 1");
$totalProyectos = $stmt->fetch()['total_proyectos'];

$stmt = $db->query("SELECT COUNT(*) as total_pacientes FROM pacientes WHERE activo = 1");
$totalPacientes = $stmt->fetch()['total_pacientes'];

// Estadísticas por rol
$stmt = $db->query("SELECT rol, COUNT(*) as cantidad FROM usuarios WHERE activo = 1 GROUP BY rol");
$usuariosPorRol = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">
            <i class="fas fa-cogs me-2"></i>Panel de Administración
        </h2>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-2">
        <div class="card" style="background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%); color: white;">
            <div class="card-body text-center">
                <h4 class="card-title"><?php echo $totalUsuarios; ?></h4>
                <p class="card-text">Usuarios</p>
                <i class="fas fa-users fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card" style="background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%); color: white;">
            <div class="card-body text-center">
                <h4 class="card-title"><?php echo $totalCentros; ?></h4>
                <p class="card-text">Centros</p>
                <i class="fas fa-hospital fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card" style="background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%); color: white;">
            <div class="card-body text-center">
                <h4 class="card-title"><?php echo $totalLaboratorios; ?></h4>
                <p class="card-text">Patrocinantes</p>
                <i class="fas fa-flask fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card" style="background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%); color: white;">
            <div class="card-body text-center">
                <h4 class="card-title"><?php echo $totalProyectos; ?></h4>
                <p class="card-text">Proyectos</p>
                <i class="fas fa-project-diagram fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card" style="background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%); color: white;">
            <div class="card-body text-center">
                <h4 class="card-title"><?php echo $totalPacientes; ?></h4>
                <p class="card-text">Pacientes</p>
                <i class="fas fa-user-injured fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card" style="background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%); color: white;">
            <div class="card-body text-center">
                <h4 class="card-title"><?php echo count($usuariosPorRol); ?></h4>
                <p class="card-text">Roles</p>
                <i class="fas fa-user-tag fa-2x"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: #9C27B0; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-cog me-2"></i>Gestión de Usuarios
                </h5>
            </div>
            <div class="card-body">
                <p class="card-text">Administra usuarios del sistema por rol.</p>
                <div class="d-grid gap-2">
                    <a href="usuarios.php" class="btn" style="background: #9C27B0; color: white; border: none;">
                        <i class="fas fa-users me-1"></i>Gestionar Usuarios
                    </a>
                    <a href="buddys.php" class="btn" style="background: #9C27B0; color: white; border: none;">
                        <i class="fas fa-user-friends me-1"></i>Gestionar BUDDYs
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: #9C27B0; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-building me-2"></i>Gestión de Entidades
                </h5>
            </div>
            <div class="card-body">
                <p class="card-text">Administra centros médicos y patrocinantes.</p>
                <div class="d-grid gap-2">
                    <a href="centros.php" class="btn" style="background: #9C27B0; color: white; border: none;">
                        <i class="fas fa-hospital me-1"></i>Gestionar Centros
                    </a>
                    <a href="laboratorios.php" class="btn" style="background: #9C27B0; color: white; border: none;">
                        <i class="fas fa-flask me-1"></i>Gestionar Patrocinantes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: #9C27B0; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-project-diagram me-2"></i>Gestión de Proyectos
                </h5>
            </div>
            <div class="card-body">
                <p class="card-text">Administra proyectos de investigación.</p>
                <a href="proyectos.php" class="btn" style="background: #9C27B0; color: white; border: none;">
                    <i class="fas fa-list me-1"></i>Gestionar Proyectos
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: #9C27B0; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Reportes del Sistema
                </h5>
            </div>
            <div class="card-body">
                <p class="card-text">Genera reportes y estadísticas del sistema.</p>
                <a href="reportes.php" class="btn" style="background: #9C27B0; color: white; border: none;">
                    <i class="fas fa-chart-line me-1"></i>Ver Reportes
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico de usuarios por rol -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #9C27B0; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Distribución de Usuarios por Rol
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($usuariosPorRol as $rol): ?>
                    <div class="col-md-3 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <?php
                                $icon = '';
                                $color = '';
                                switch($rol['rol']) {
                                    case 'CENTRO':
                                        $icon = 'fa-hospital';
                                        $color = '#4FC3F7';
                                        break;
                                    case 'LABORATORIO':
                                        $icon = 'fa-flask';
                                        $color = '#FF7043';
                                        break;
                                    case 'BUDDY':
                                        $icon = 'fa-user-friends';
                                        $color = '#66BB6A';
                                        break;
                                    case 'ADMIN':
                                        $icon = 'fa-cogs';
                                        $color = '#9C27B0';
                                        break;
                                }
                                ?>
                                <i class="fas <?php echo $icon; ?> fa-2x" style="color: <?php echo $color; ?>;"></i>
                            </div>
                            <div>
                                <h6 class="mb-0"><?php echo $rol['rol']; ?></h6>
                                <span class="badge" style="background: <?php echo $color; ?>; color: white;">
                                    <?php echo $rol['cantidad']; ?> usuarios
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
