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

// Obtener ID del paciente
$paciente_id = $_GET['id'] ?? null;

if (!$paciente_id) {
    header('Location: pacientes.php');
    exit();
}

// Obtener información del paciente
$stmt = $db->prepare("
    SELECT p.*, pr.nombre as proyecto_nombre, pr.descripcion as proyecto_descripcion,
           l.nombre as laboratorio_nombre
    FROM pacientes p
    LEFT JOIN proyectos pr ON p.proyecto_id = pr.id
    LEFT JOIN laboratorios l ON pr.patrocinante_id = l.id
    WHERE p.id = ? AND p.centro_id = ? AND p.activo = 1
");
$stmt->execute([$paciente_id, $user['identidad']]);
$paciente = $stmt->fetch();

if (!$paciente) {
    header('Location: pacientes.php?error=4');
    exit();
}
?>

<?php
$pageTitle = 'Detalles del Paciente';
$rolColor = '#4FC3F7'; // Azul de la imagen
include '../includes/header.php';
?>

<!-- Mensajes de error -->
<?php if (isset($_GET['error']) && $_GET['error'] == '4'): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>Paciente no encontrado o no tiene permisos para verlo.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-user me-2"></i>Detalles del Paciente
            </h2>
            <div>
                <a href="pacientes.php" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
                <a href="editar_paciente.php?id=<?php echo $paciente['id']; ?>" class="btn" style="background: #4FC3F7; color: white; border: none;">
                    <i class="fas fa-edit me-1"></i>Editar
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Información Personal -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header" style="background: #4FC3F7; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>Información Personal
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4"><strong>Nombre:</strong></div>
                    <div class="col-sm-8"><?php echo htmlspecialchars($paciente['nombre']); ?></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Apellido:</strong></div>
                    <div class="col-sm-8"><?php echo htmlspecialchars($paciente['apellido']); ?></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Email:</strong></div>
                    <div class="col-sm-8">
                        <?php if ($paciente['email']): ?>
                            <a href="mailto:<?php echo htmlspecialchars($paciente['email']); ?>">
                                <?php echo htmlspecialchars($paciente['email']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">No especificado</span>
                        <?php endif; ?>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Teléfono 1:</strong></div>
                    <div class="col-sm-8">
                        <?php if ($paciente['telefono1']): ?>
                            <a href="tel:<?php echo htmlspecialchars($paciente['telefono1']); ?>">
                                <?php echo htmlspecialchars($paciente['telefono1']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">No especificado</span>
                        <?php endif; ?>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Teléfono 2:</strong></div>
                    <div class="col-sm-8">
                        <?php if ($paciente['telefono2']): ?>
                            <a href="tel:<?php echo htmlspecialchars($paciente['telefono2']); ?>">
                                <?php echo htmlspecialchars($paciente['telefono2']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">No especificado</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Domicilio -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header" style="background: #4FC3F7; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-home me-2"></i>Domicilio
                </h5>
            </div>
            <div class="card-body">
                <?php if ($paciente['domicilio_calle'] || $paciente['domicilio_numero']): ?>
                    <div class="row">
                        <div class="col-sm-4"><strong>Dirección:</strong></div>
                        <div class="col-sm-8">
                            <?php 
                            $direccion = trim($paciente['domicilio_calle'] . ' ' . $paciente['domicilio_numero']);
                            if ($paciente['domicilio_piso']) $direccion .= ', Piso ' . $paciente['domicilio_piso'];
                            if ($paciente['domicilio_depto']) $direccion .= ', Depto ' . $paciente['domicilio_depto'];
                            echo htmlspecialchars($direccion);
                            ?>
                        </div>
                    </div>
                    <hr>
                <?php endif; ?>
                
                <?php if ($paciente['domicilio_localidad']): ?>
                    <div class="row">
                        <div class="col-sm-4"><strong>Localidad:</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($paciente['domicilio_localidad']); ?></div>
                    </div>
                    <hr>
                <?php endif; ?>
                
                <?php if ($paciente['domicilio_provincia']): ?>
                    <div class="row">
                        <div class="col-sm-4"><strong>Provincia:</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($paciente['domicilio_provincia']); ?></div>
                    </div>
                    <hr>
                <?php endif; ?>
                
                <?php if (!$paciente['domicilio_calle'] && !$paciente['domicilio_localidad']): ?>
                    <div class="text-center text-muted">
                        <i class="fas fa-info-circle me-2"></i>No se ha registrado información de domicilio
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Contacto de Emergencia -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header" style="background: #4FC3F7; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-phone me-2"></i>Contacto de Emergencia
                </h5>
            </div>
            <div class="card-body">
                <?php if ($paciente['familiar_contacto'] || $paciente['telefono_familiar']): ?>
                    <?php if ($paciente['familiar_contacto']): ?>
                        <div class="row">
                            <div class="col-sm-4"><strong>Familiar:</strong></div>
                            <div class="col-sm-8"><?php echo htmlspecialchars($paciente['familiar_contacto']); ?></div>
                        </div>
                        <hr>
                    <?php endif; ?>
                    
                    <?php if ($paciente['telefono_familiar']): ?>
                        <div class="row">
                            <div class="col-sm-4"><strong>Teléfono:</strong></div>
                            <div class="col-sm-8">
                                <a href="tel:<?php echo htmlspecialchars($paciente['telefono_familiar']); ?>">
                                    <?php echo htmlspecialchars($paciente['telefono_familiar']); ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center text-muted">
                        <i class="fas fa-info-circle me-2"></i>No se ha registrado contacto de emergencia
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Información del Estudio -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header" style="background: #4FC3F7; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-flask me-2"></i>Información del Estudio
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4"><strong>Proyecto:</strong></div>
                    <div class="col-sm-8">
                        <?php if ($paciente['proyecto_nombre']): ?>
                            <span class="badge" style="background: #4FC3F7; color: white;">
                                <?php echo htmlspecialchars($paciente['proyecto_nombre']); ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">Sin proyecto asignado</span>
                        <?php endif; ?>
                    </div>
                </div>
                <hr>
                
                <?php if ($paciente['proyecto_descripcion']): ?>
                    <div class="row">
                        <div class="col-sm-4"><strong>Descripción:</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($paciente['proyecto_descripcion']); ?></div>
                    </div>
                    <hr>
                <?php endif; ?>
                
                <?php if ($paciente['laboratorio_nombre']): ?>
                    <div class="row">
                        <div class="col-sm-4"><strong>Patrocinante:</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($paciente['laboratorio_nombre']); ?></div>
                    </div>
                    <hr>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-sm-4"><strong>Consentimiento:</strong></div>
                    <div class="col-sm-8">
                        <?php if ($paciente['consentimiento_firmado'] == 'SI'): ?>
                            <span class="badge bg-success">Firmado</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Pendiente</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comentarios -->
<?php if ($paciente['comentarios']): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #4FC3F7; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-comment me-2"></i>Comentarios
                </h5>
            </div>
            <div class="card-body">
                <p><?php echo nl2br(htmlspecialchars($paciente['comentarios'])); ?></p>
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
                        <strong>ID del Paciente:</strong><br>
                        <span class="text-muted"><?php echo $paciente['id']; ?></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Fecha de Registro:</strong><br>
                        <span class="text-muted"><?php echo date('d/m/Y H:i', strtotime($paciente['fecha_alta'])); ?></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Estado:</strong><br>
                        <span class="badge bg-success">Activo</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Centro:</strong><br>
                        <span class="text-muted">ID: <?php echo $paciente['centro_id']; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
