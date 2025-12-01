<?php
$pageTitle = 'Patrocinante - Dashboard';
$rolColor = '#FF7043'; // Naranja de la imagen
include '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Obtener estadísticas para el laboratorio
$stmt = $db->query("SELECT COUNT(*) as total_proyectos FROM proyectos WHERE activo = 1");
$totalProyectos = $stmt->fetch()['total_proyectos'];

$stmt = $db->query("SELECT COUNT(*) as mis_proyectos FROM proyectos p 
                   JOIN laboratorios l ON p.patrocinante_id = l.id 
                   WHERE l.nombre = 'Laboratorio Roche España' AND p.activo = 1");
$misProyectos = $stmt->fetch()['mis_proyectos'];

$stmt = $db->query("SELECT COUNT(*) as total_centros FROM centros WHERE activo = 1");
$totalCentros = $stmt->fetch()['total_centros'];
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">
            <i class="fas fa-flask me-2"></i>Panel de Patrocinante
        </h2>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card" style="background: linear-gradient(135deg, #FF7043 0%, #FF5722 100%); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $misProyectos; ?></h4>
                        <p class="card-text">Mis Proyectos</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-project-diagram fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card" style="background: linear-gradient(135deg, #FF7043 0%, #FF5722 100%); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $totalProyectos; ?></h4>
                        <p class="card-text">Total Proyectos</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card" style="background: linear-gradient(135deg, #FF7043 0%, #FF5722 100%); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $totalCentros; ?></h4>
                        <p class="card-text">Centros Colaboradores</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-hospital fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: #FF7043; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-plus-circle me-2"></i>Gestión de Proyectos
                </h5>
            </div>
            <div class="card-body">
                <p class="card-text">Crea y administra tus proyectos de investigación.</p>
                <a href="proyectos.php" class="btn" style="background: #FF7043; color: white; border: none;">
                    <i class="fas fa-eye me-1"></i>Ver Proyectos
                </a>
                <a href="nuevo_proyecto.php" class="btn" style="background: #FF7043; color: white; border: none;">
                    <i class="fas fa-plus me-1"></i>Nuevo Proyecto
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: #FF7043; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>Centros Colaboradores
                </h5>
            </div>
            <div class="card-body">
                <p class="card-text">Visualiza los centros médicos disponibles para colaboración.</p>
                <a href="centros.php" class="btn" style="background: #FF7043; color: white; border: none;">
                    <i class="fas fa-hospital me-1"></i>Ver Centros
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
