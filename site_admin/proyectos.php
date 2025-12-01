<?php
$pageTitle = 'Gestión de Proyectos';
$rolColor = '#9C27B0'; // Púrpura para ADMIN
include '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Obtener todos los proyectos con información del laboratorio y cantidad de eventos
$stmt = $db->query("
    SELECT p.*, l.nombre as laboratorio_nombre, l.pais as laboratorio_pais,
           COALESCE(e.total_eventos, 0) as total_eventos
    FROM proyectos p 
    JOIN laboratorios l ON p.patrocinante_id = l.id 
    LEFT JOIN (
        SELECT proyecto_id, COUNT(*) as total_eventos 
        FROM eventos_tratamiento 
        WHERE activo = 1 
        GROUP BY proyecto_id
    ) e ON p.id = e.proyecto_id
    WHERE p.activo = 1 
    ORDER BY p.fecha_alta DESC
");
$proyectos = $stmt->fetchAll();

// Obtener laboratorios para el formulario
$stmt = $db->query("SELECT * FROM laboratorios WHERE activo = 1 ORDER BY nombre");
$laboratorios = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-project-diagram me-2"></i>Gestión de Proyectos
            </h2>
            <button class="btn" style="background: #9C27B0; color: white; border: none;" data-bs-toggle="modal" data-bs-target="#nuevoProyectoModal">
                <i class="fas fa-plus me-1"></i>Nuevo Proyecto
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead style="background: #9C27B0; color: white;">
                            <tr>
                                <th>ID</th>
                                <th>Nombre del Proyecto</th>
                                <th>Patrocinante</th>
                                <th>País</th>
                                <th>Descripción</th>
                                <th>Tratamiento</th>
                                <th>Fecha de Alta</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proyectos as $proyecto): ?>
                            <tr>
                                <td><?php echo $proyecto['id']; ?></td>
                                <td><?php echo htmlspecialchars($proyecto['nombre']); ?></td>
                                <td>
                                    <span class="badge" style="background: #9C27B0; color: white;">
                                        <?php echo htmlspecialchars($proyecto['laboratorio_nombre']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($proyecto['laboratorio_pais']); ?></td>
                                <td><?php echo htmlspecialchars(substr($proyecto['descripcion'], 0, 50)) . '...'; ?></td>
                                <td>
                                    <span class="badge <?php echo $proyecto['total_eventos'] > 0 ? 'bg-success' : 'bg-secondary'; ?>">
                                        <i class="fas fa-calendar me-1"></i><?php echo $proyecto['total_eventos']; ?> eventos
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($proyecto['fecha_alta'])); ?></td>
                                <td>
                                    <a href="centros_proyecto.php?id=<?php echo $proyecto['id']; ?>" class="btn btn-sm" style="background: #9C27B0; color: white; border: none;" title="Ver Centros del Proyecto">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-warning" onclick="editarProyecto(<?php echo $proyecto['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="tratamiento.php?id=<?php echo $proyecto['id']; ?>" class="btn btn-sm btn-outline-info" title="Tratamiento">
                                        <i class="fas fa-calendar"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarProyecto(<?php echo $proyecto['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Proyecto -->
<div class="modal fade" id="nuevoProyectoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #9C27B0; color: white;">
                <h5 class="modal-title">Nuevo Proyecto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formProyecto" method="POST" action="guardar_proyecto.php">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del Proyecto *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="patrocinante_id" class="form-label">Patrocinante *</label>
                                <select class="form-select" id="patrocinante_id" name="patrocinante_id" required>
                                    <option value="">Seleccionar Patrocinante</option>
                                    <?php foreach ($laboratorios as $lab): ?>
                                        <option value="<?php echo $lab['id']; ?>"><?php echo htmlspecialchars($lab['nombre']); ?> (<?php echo htmlspecialchars($lab['pais']); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción *</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background: #9C27B0; color: white; border: none;">Guardar Proyecto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarProyecto(id) {
    alert('Función de editar proyecto en desarrollo para ID: ' + id);
}

function eliminarProyecto(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este proyecto?')) {
        alert('Función de eliminar proyecto en desarrollo para ID: ' + id);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
