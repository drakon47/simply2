<?php
$pageTitle = 'Gestión de BUDDYs';
$rolColor = '#9C27B0'; // Púrpura para ADMIN
include '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Obtener todos los usuarios BUDDY
$stmt = $db->query("SELECT * FROM usuarios WHERE rol = 'BUDDY' AND activo = 1 ORDER BY nombre");
$buddys = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-user-friends me-2"></i>Gestión de BUDDYs
            </h2>
            <button class="btn" style="background: #9C27B0; color: white; border: none;" data-bs-toggle="modal" data-bs-target="#nuevoBuddyModal">
                <i class="fas fa-plus me-1"></i>Nuevo BUDDY
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
                                <th>Apellido</th>
                                <th>Email</th>
                                <th>Fecha Registro</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($buddys as $buddy): ?>
                            <tr>
                                <td><?php echo $buddy['id']; ?></td>
                                <td><?php echo htmlspecialchars($buddy['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($buddy['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($buddy['email']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($buddy['fecha_registro'])); ?></td>
                                <td>
                                    <?php if ($buddy['activo']): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm" style="background: #9C27B0; color: white; border: none;" onclick="editarBuddy(<?php echo $buddy['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="toggleBuddy(<?php echo $buddy['id']; ?>, <?php echo $buddy['activo'] ? 'false' : 'true'; ?>)">
                                        <i class="fas fa-<?php echo $buddy['activo'] ? 'ban' : 'check'; ?>"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarBuddy(<?php echo $buddy['id']; ?>)">
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

<!-- Modal Nuevo BUDDY -->
<div class="modal fade" id="nuevoBuddyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #9C27B0; color: white;">
                <h5 class="modal-title">Nuevo BUDDY</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formBuddy" method="POST" action="guardar_buddy.php">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="apellido" class="form-label">Apellido *</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background: #9C27B0; color: white; border: none;">Guardar BUDDY</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarBuddy(id) {
    alert('Función de edición en desarrollo para BUDDY ID: ' + id);
}

function toggleBuddy(id, activo) {
    const accion = activo === 'true' ? 'activar' : 'desactivar';
    if (confirm(`¿Estás seguro de que quieres ${accion} este BUDDY?`)) {
        alert(`Función de ${accion} en desarrollo para BUDDY ID: ` + id);
    }
}

function eliminarBuddy(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este BUDDY?')) {
        alert('Función de eliminación en desarrollo para BUDDY ID: ' + id);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
