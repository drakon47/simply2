<?php
$pageTitle = 'Buddy - Dashboard';
$rolColor = '#66BB6A'; // Verde de la imagen
include '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Obtener estadísticas para el buddy
$stmt = $db->query("SELECT COUNT(*) as total_proyectos FROM proyectos WHERE activo = 1");
$totalProyectos = $stmt->fetch()['total_proyectos'];

$stmt = $db->query("SELECT COUNT(*) as total_centros FROM centros WHERE activo = 1");
$totalCentros = $stmt->fetch()['total_centros'];

$stmt = $db->query("SELECT COUNT(*) as total_laboratorios FROM laboratorios WHERE activo = 1");
$totalLaboratorios = $stmt->fetch()['total_laboratorios'];
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">
            <i class="fas fa-user-friends me-2"></i>Panel de Buddy
        </h2>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card" style="background: linear-gradient(135deg, #66BB6A 0%, #4CAF50 100%); color: white;">
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
    <div class="col-md-4">
        <div class="card" style="background: linear-gradient(135deg, #66BB6A 0%, #4CAF50 100%); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $totalCentros; ?></h4>
                        <p class="card-text">Centros Disponibles</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-hospital fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card" style="background: linear-gradient(135deg, #66BB6A 0%, #4CAF50 100%); color: white;">
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

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: #66BB6A; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-search me-2"></i>Explorar Proyectos
                </h5>
            </div>
            <div class="card-body">
                <p class="card-text">Explora y busca proyectos disponibles para colaboración.</p>
                <a href="proyectos.php" class="btn" style="background: #66BB6A; color: white; border: none;">
                    <i class="fas fa-eye me-1"></i>Ver Proyectos
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: #66BB6A; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-map-marker-alt me-2"></i>Centros Cercanos
                </h5>
            </div>
            <div class="card-body">
                <p class="card-text">Encuentra centros médicos en tu área para colaborar.</p>
                <a href="centros.php" class="btn" style="background: #66BB6A; color: white; border: none;">
                    <i class="fas fa-hospital me-1"></i>Ver Centros
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #66BB6A; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-handshake me-2"></i>Mis Colaboraciones
                </h5>
            </div>
            <div class="card-body">
                <p class="card-text">Gestiona tus colaboraciones activas con centros y patrocinantes.</p>
                <a href="colaboraciones.php" class="btn" style="background: #66BB6A; color: white; border: none;">
                    <i class="fas fa-list me-1"></i>Ver Colaboraciones
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
