<?php
$pageTitle = 'Gestión de Patrocinantes';
$rolColor = '#9C27B0'; // Púrpura para ADMIN
include '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Obtener todos los laboratorios
$stmt = $db->query("SELECT * FROM laboratorios WHERE activo = 1 ORDER BY nombre");
$laboratorios = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-flask me-2"></i>Gestión de Patrocinantes
            </h2>
            <button class="btn" style="background: #9C27B0; color: white; border: none;" data-bs-toggle="modal" data-bs-target="#nuevoLaboratorioModal">
                <i class="fas fa-plus me-1"></i>Nuevo Patrocinante
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
                                <th>Nombre</th>
                                <th>País</th>
                                <th>Fecha de Alta</th>
                                <th>Proyectos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($laboratorios as $laboratorio): ?>
                            <?php
                            // Contar proyectos del laboratorio
                            $stmt = $db->prepare("SELECT COUNT(*) as total FROM proyectos WHERE patrocinante_id = ? AND activo = 1");
                            $stmt->execute([$laboratorio['id']]);
                            $proyectosCount = $stmt->fetch()['total'];
                            ?>
                            <tr>
                                <td><?php echo $laboratorio['id']; ?></td>
                                <td><?php echo htmlspecialchars($laboratorio['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($laboratorio['pais']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($laboratorio['fecha_alta'])); ?></td>
                                <td>
                                    <span class="badge" style="background: #9C27B0; color: white;">
                                        <?php echo $proyectosCount; ?> proyectos
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm" style="background: #9C27B0; color: white; border: none;" onclick="editarLaboratorio(<?php echo $laboratorio['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarLaboratorio(<?php echo $laboratorio['id']; ?>)">
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

<!-- Modal Nuevo Patrocinante -->
<div class="modal fade" id="nuevoLaboratorioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #9C27B0; color: white;">
                <h5 class="modal-title">Nuevo Patrocinante</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formLaboratorio" method="POST" action="guardar_laboratorio.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Patrocinante *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="pais" class="form-label">País *</label>
                        <input type="text" class="form-control" id="pais" name="pais" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background: #9C27B0; color: white; border: none;">Guardar Patrocinante</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarLaboratorio(id) {
    alert('Función de edición en desarrollo para patrocinante ID: ' + id);
}

function eliminarLaboratorio(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este patrocinante?')) {
        alert('Función de eliminación en desarrollo para patrocinante ID: ' + id);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
