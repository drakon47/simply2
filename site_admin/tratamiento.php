<?php
$pageTitle = 'Tratamiento del Proyecto';
$rolColor = '#9C27B0'; // Púrpura para ADMIN
include '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Obtener ID del proyecto
$proyecto_id = $_GET['id'] ?? null;

if (!$proyecto_id) {
    header('Location: proyectos.php');
    exit();
}

// Obtener información del proyecto
$stmt = $db->prepare("
    SELECT p.*, l.nombre as laboratorio_nombre, l.pais as laboratorio_pais
    FROM proyectos p 
    JOIN laboratorios l ON p.patrocinante_id = l.id 
    WHERE p.id = ? AND p.activo = 1
");
$stmt->execute([$proyecto_id]);
$proyecto = $stmt->fetch();

if (!$proyecto) {
    header('Location: proyectos.php');
    exit();
}

// Obtener eventos de tratamiento ordenados por días
$stmt = $db->prepare("
    SELECT * FROM eventos_tratamiento 
    WHERE proyecto_id = ? AND activo = 1 
    ORDER BY dias_desde_inicio ASC
");
$stmt->execute([$proyecto_id]);
$eventos = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>
                    <i class="fas fa-calendar-medical me-2"></i>Tratamiento del Proyecto
                </h2>
                <h4 class="text-muted"><?php echo htmlspecialchars($proyecto['nombre']); ?></h4>
            </div>
            <a href="proyectos.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver a Proyectos
            </a>
        </div>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>Evento guardado exitosamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Error al procesar la solicitud.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Información del Proyecto -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #9C27B0; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Información del Proyecto
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Patrocinante:</strong> <?php echo htmlspecialchars($proyecto['laboratorio_nombre']); ?></p>
                        <p><strong>País:</strong> <?php echo htmlspecialchars($proyecto['laboratorio_pais']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Fecha de Alta:</strong> <?php echo date('d/m/Y', strtotime($proyecto['fecha_alta'])); ?></p>
                        <p><strong>Total de Eventos:</strong> <span class="badge" style="background: #9C27B0; color: white;"><?php echo count($eventos); ?></span></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <p><strong>Descripción:</strong></p>
                        <p><?php echo htmlspecialchars($proyecto['descripcion']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Agregar Nuevo Evento -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #9C27B0; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-plus me-2"></i>Agregar Nuevo Evento
                </h5>
            </div>
            <div class="card-body">
                <form id="formEvento" method="POST" action="guardar_evento.php">
                    <input type="hidden" name="proyecto_id" value="<?php echo $proyecto_id; ?>">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="titulo" class="form-label">Título del Evento *</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="dias_desde_inicio" class="form-label">Días desde Inicio *</label>
                                <input type="number" class="form-control" id="dias_desde_inicio" name="dias_desde_inicio" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="tipo_evento" class="form-label">Tipo de Evento *</label>
                                <select class="form-select" id="tipo_evento" name="tipo_evento" required>
                                    <option value="">Seleccionar tipo</option>
                                    <option value="Presencial">Presencial</option>
                                    <option value="Virtual">Virtual</option>
                                    <option value="Llamado">Llamado</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn" style="background: #9C27B0; color: white; border: none;">
                            <i class="fas fa-plus me-1"></i>Agregar Evento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Eventos -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #9C27B0; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Cronograma de Tratamiento
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($eventos)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay eventos de tratamiento registrados</h5>
                        <p class="text-muted">Agrega el primer evento usando el formulario de arriba.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead style="background: #9C27B0; color: white;">
                                <tr>
                                    <th>Día</th>
                                    <th>Título</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($eventos as $evento): ?>
                                <tr>
                                    <td>
                                        <span class="badge" style="background: #9C27B0; color: white;">
                                            Día <?php echo $evento['dias_desde_inicio']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($evento['titulo']); ?></td>
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
                                        <span class="badge <?php echo $color; ?>"><?php echo $evento['tipo_evento']; ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($evento['descripcion']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($evento['fecha_creacion'])); ?></td>
                                    <td>
                                        <a href="editar_evento.php?id=<?php echo $evento['id']; ?>&proyecto_id=<?php echo $proyecto_id; ?>" class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="eliminar_evento.php?id=<?php echo $evento['id']; ?>&proyecto_id=<?php echo $proyecto_id; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar este evento?')">
                                            <i class="fas fa-trash"></i>
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

<script>
function editarEvento(id) {
    alert('Función de editar evento en desarrollo para ID: ' + id);
}

function eliminarEvento(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este evento?')) {
        // Implementar eliminación
        alert('Función de eliminar evento en desarrollo para ID: ' + id);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
