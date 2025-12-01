<?php
$pageTitle = 'Gestión de Centros';
$rolColor = '#4FC3F7'; // Azul de la imagen
include '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Obtener todos los centros
$stmt = $db->query("SELECT * FROM centros WHERE activo = 1 ORDER BY nombre");
$centros = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-hospital me-2"></i>Gestión de Centros
            </h2>
            <button class="btn" style="background: #4FC3F7; color: white; border: none;" data-bs-toggle="modal" data-bs-target="#nuevoCentroModal">
                <i class="fas fa-plus me-1"></i>Nuevo Centro
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
                        <thead style="background: #4FC3F7; color: white;">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Dirección</th>
                                <th>Localidad</th>
                                <th>Provincia</th>
                                <th>País</th>
                                <th>Email 1</th>
                                <th>Email 2</th>
                                <th>Teléfono</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($centros as $centro): ?>
                            <tr>
                                <td><?php echo $centro['id']; ?></td>
                                <td><?php echo htmlspecialchars($centro['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($centro['direccion']); ?></td>
                                <td><?php echo htmlspecialchars($centro['localidad']); ?></td>
                                <td><?php echo htmlspecialchars($centro['provincia']); ?></td>
                                <td><?php echo htmlspecialchars($centro['pais']); ?></td>
                                <td><?php echo htmlspecialchars($centro['email_referencia']); ?></td>
                                <td><?php echo htmlspecialchars($centro['email_referencia_2']); ?></td>
                                <td><?php echo htmlspecialchars($centro['telefono']); ?></td>
                                <td>
                                    <button class="btn btn-sm" style="background: #4FC3F7; color: white; border: none;" onclick="editarCentro(<?php echo $centro['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarCentro(<?php echo $centro['id']; ?>)">
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

<!-- Modal Nuevo Centro -->
<div class="modal fade" id="nuevoCentroModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #4FC3F7; color: white;">
                <h5 class="modal-title">Nuevo Centro</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCentro" method="POST" action="guardar_centro.php">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del Centro</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="localidad" class="form-label">Localidad</label>
                                <input type="text" class="form-control" id="localidad" name="localidad" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="provincia" class="form-label">Provincia</label>
                                <input type="text" class="form-control" id="provincia" name="provincia" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="pais" class="form-label">País</label>
                                <input type="text" class="form-control" id="pais" name="pais" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="email_referencia" class="form-label">Email de Referencia</label>
                                <input type="email" class="form-control" id="email_referencia" name="email_referencia">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="email_referencia_2" class="form-label">Email de Referencia 2</label>
                                <input type="email" class="form-control" id="email_referencia_2" name="email_referencia_2">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background: #4FC3F7; color: white; border: none;">Guardar Centro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarCentro(id) {
    alert('Función de edición en desarrollo para centro ID: ' + id);
}

function eliminarCentro(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este centro?')) {
        alert('Función de eliminación en desarrollo para centro ID: ' + id);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
