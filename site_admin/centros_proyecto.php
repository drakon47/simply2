<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getUserInfo();

// Solo usuarios ADMIN pueden acceder
if ($user['rol'] !== 'ADMIN') {
    header('Location: ../index.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener parámetros
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
    header('Location: proyectos.php?error=proyecto_no_encontrado');
    exit();
}

// Obtener centros asociados al proyecto
$stmt = $db->prepare("
    SELECT c.*, cp.fecha_asociacion 
    FROM centros c 
    INNER JOIN centros_proyectos cp ON c.id = cp.centro_id 
    WHERE cp.proyecto_id = ? AND cp.activo = 1 AND c.activo = 1
    ORDER BY c.nombre
");
$stmt->execute([$proyecto_id]);
$centros = $stmt->fetchAll();

// Contar cantidad de pacientes en el proyecto
$stmt = $db->prepare("SELECT COUNT(*) as total FROM pacientes WHERE proyecto_id = ? AND activo = 1");
$stmt->execute([$proyecto_id]);
$totalPacientes = $stmt->fetch()['total'];

// Obtener eventos de tratamiento del proyecto
$stmt = $db->prepare("
    SELECT * FROM eventos_tratamiento 
    WHERE proyecto_id = ? AND activo = 1 
    ORDER BY dias_desde_inicio ASC
");
$stmt->execute([$proyecto_id]);
$eventos = $stmt->fetchAll();

// Obtener documentos del proyecto
$stmt = $db->prepare("
    SELECT * FROM documentos_proyecto 
    WHERE proyecto_id = ? AND activo = 1 
    ORDER BY fecha_subida DESC
");
$stmt->execute([$proyecto_id]);
$documentos = $stmt->fetchAll();

?>

<?php
$pageTitle = 'Centros del Proyecto: ' . $proyecto['nombre'];
$rolColor = '#9C27B0'; // Púrpura para ADMIN
include '../includes/header.php';
?>

<!-- Mensajes de error -->
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>Proyecto no encontrado.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['success_evento'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>Evento guardado exitosamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error_evento'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>Error al procesar la solicitud.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['success_documento'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>Documento subido exitosamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error_documento'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>Error al subir el documento.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-hospital me-2"></i>Centros del Proyecto: <?php echo htmlspecialchars($proyecto['nombre']); ?>
            </h2>
            <a href="proyectos.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver a Proyectos
            </a>
        </div>
    </div>
</div>

<!-- Información del Proyecto -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #9C27B0; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-project-diagram me-2"></i>Información del Proyecto
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Nombre:</strong><br>
                        <?php echo htmlspecialchars($proyecto['nombre']); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Patrocinante:</strong><br>
                        <span class="badge" style="background: #9C27B0; color: white;">
                            <?php echo htmlspecialchars($proyecto['laboratorio_nombre']); ?>
                        </span>
                    </div>
                    <div class="col-md-3">
                        <strong>País:</strong><br>
                        <?php echo htmlspecialchars($proyecto['laboratorio_pais']); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Total Pacientes:</strong><br>
                        <span class="badge bg-info"><?php echo $totalPacientes; ?> pacientes</span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <strong>Descripción:</strong><br>
                        <?php echo htmlspecialchars($proyecto['descripcion']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Centros Asociados -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #28a745; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-hospital me-2"></i>Centros Asociados (<?php echo count($centros); ?>)
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($centros)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                        <p>No hay centros asociados a este proyecto.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre del Centro</th>
                                    <th>Dirección</th>
                                    <th>Localidad</th>
                                    <th>Provincia</th>
                                    <th>País</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Fecha de Asociación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($centros as $centro): ?>
                                <tr>
                                    <td><?php echo $centro['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($centro['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($centro['direccion']); ?></td>
                                    <td><?php echo htmlspecialchars($centro['localidad']); ?></td>
                                    <td><?php echo htmlspecialchars($centro['provincia']); ?></td>
                                    <td><?php echo htmlspecialchars($centro['pais']); ?></td>
                                    <td>
                                        <?php if ($centro['email_referencia']): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($centro['email_referencia']); ?>">
                                                <?php echo htmlspecialchars($centro['email_referencia']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($centro['telefono']): ?>
                                            <a href="tel:<?php echo htmlspecialchars($centro['telefono']); ?>">
                                                <?php echo htmlspecialchars($centro['telefono']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($centro['fecha_asociacion'])); ?></td>
                                    <td>
                                        <a href="ver_centro.php?id=<?php echo $centro['id']; ?>" class="btn btn-sm" style="background: #9C27B0; color: white; border: none;" title="Ver Ficha del Centro">
                                            <i class="fas fa-eye"></i>
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

<!-- Eventos de Tratamiento -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: #17a2b8; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-medical me-2"></i>Eventos de Tratamiento (<?php echo count($eventos); ?>)
                </h5>
                <button class="btn btn-sm" style="background: white; color: #17a2b8; border: none;" data-bs-toggle="modal" data-bs-target="#nuevoEventoModal">
                    <i class="fas fa-plus me-1"></i>Agregar Evento
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($eventos)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                        <p>No hay eventos de tratamiento asociados a este proyecto.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Días desde Inicio</th>
                                    <th>Tipo de Evento</th>
                                    <th>Descripción</th>
                                    <th>Fecha de Creación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($eventos as $evento): ?>
                                <tr>
                                    <td><?php echo $evento['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($evento['titulo']); ?></strong></td>
                                    <td>
                                        <span class="badge bg-primary">Día <?php echo $evento['dias_desde_inicio']; ?></span>
                                    </td>
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
                                        <span class="badge <?php echo $color; ?>"><?php echo htmlspecialchars($evento['tipo_evento']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($evento['descripcion']): ?>
                                            <?php echo htmlspecialchars($evento['descripcion']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Sin descripción</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($evento['fecha_creacion'])); ?></td>
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

<!-- Modal Nuevo Evento -->
<div class="modal fade" id="nuevoEventoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #17a2b8; color: white;">
                <h5 class="modal-title">Agregar Nuevo Evento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEvento" method="POST" action="guardar_evento.php">
                <div class="modal-body">
                    <input type="hidden" name="proyecto_id" value="<?php echo $proyecto_id; ?>">
                    <input type="hidden" name="redirect" value="centros_proyecto.php">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="titulo" class="form-label">Título del Evento *</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dias_desde_inicio" class="form-label">Días desde Inicio *</label>
                                <input type="number" class="form-control" id="dias_desde_inicio" name="dias_desde_inicio" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background: #17a2b8; color: white; border: none;">
                        <i class="fas fa-plus me-1"></i>Agregar Evento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Documentación -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: #FF9800; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>Documentación (<?php echo count($documentos); ?>)
                </h5>
                <button class="btn btn-sm" style="background: white; color: #FF9800; border: none;" data-bs-toggle="modal" data-bs-target="#nuevoDocumentoModal">
                    <i class="fas fa-plus me-1"></i>Subir Documento
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($documentos)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                        <p>No hay documentos asociados a este proyecto.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Archivo</th>
                                    <th>Fecha Vencimiento</th>
                                    <th>Descripción</th>
                                    <th>Fecha Subida</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documentos as $documento): ?>
                                <tr>
                                    <td><?php echo $documento['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($documento['titulo']); ?></strong></td>
                                    <td>
                                        <a href="../<?php echo htmlspecialchars($documento['ruta_archivo']); ?>" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-file-download me-1"></i><?php echo htmlspecialchars($documento['nombre_archivo']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($documento['fecha_vencimiento']): ?>
                                            <?php 
                                            $fecha_vencimiento = strtotime($documento['fecha_vencimiento']);
                                            $hoy = strtotime('today');
                                            $dias_restantes = ($fecha_vencimiento - $hoy) / 86400;
                                            
                                            if ($dias_restantes < 0) {
                                                $badge_class = 'bg-danger';
                                                $texto = 'Vencido';
                                            } elseif ($dias_restantes <= 30) {
                                                $badge_class = 'bg-warning';
                                                $texto = 'Por vencer';
                                            } else {
                                                $badge_class = 'bg-success';
                                                $texto = 'Vigente';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <?php echo date('d/m/Y', strtotime($documento['fecha_vencimiento'])); ?> 
                                                <small>(<?php echo $texto; ?>)</small>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Sin fecha</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($documento['descripcion']): ?>
                                            <?php echo htmlspecialchars(substr($documento['descripcion'], 0, 50)) . (strlen($documento['descripcion']) > 50 ? '...' : ''); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Sin descripción</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($documento['fecha_subida'])); ?></td>
                                    <td>
                                        <a href="../<?php echo htmlspecialchars($documento['ruta_archivo']); ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Descargar">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarDocumento(<?php echo $documento['id']; ?>, '<?php echo htmlspecialchars($documento['titulo']); ?>')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

<!-- Modal Subir Documento -->
<div class="modal fade" id="nuevoDocumentoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #FF9800; color: white;">
                <h5 class="modal-title">Subir Nuevo Documento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formDocumento" method="POST" action="guardar_documento.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="proyecto_id" value="<?php echo $proyecto_id; ?>">
                    <div class="mb-3">
                        <label for="titulo_documento" class="form-label">Título del Documento *</label>
                        <input type="text" class="form-control" id="titulo_documento" name="titulo_documento" required>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_vencimiento" class="form-label">Fecha Vencimiento</label>
                        <input type="text" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" placeholder="dd/mm/yyyy" pattern="\d{2}/\d{2}/\d{4}">
                        <small class="form-text text-muted">Formato: dd/mm/yyyy</small>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion_documento" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion_documento" name="descripcion_documento" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="archivo" class="form-label">Archivo *</label>
                        <input type="file" class="form-control" id="archivo" name="archivo" accept=".png,.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg" required>
                        <small class="form-text text-muted">Formatos permitidos: PNG, PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background: #FF9800; color: white; border: none;">
                        <i class="fas fa-upload me-1"></i>Subir Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Inicializar datepicker cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Configuración de Flatpickr con formato dd/mm/yyyy
    const configFecha = {
        dateFormat: 'd/m/Y',
        locale: 'es',
        allowInput: true,
        altInput: false
    };
    
    // Inicializar datepicker para fecha de vencimiento
    flatpickr('#fecha_vencimiento', configFecha);
});

function eliminarDocumento(id, titulo) {
    if (confirm('¿Estás seguro de que quieres eliminar el documento "' + titulo + '"?')) {
        // Implementar eliminación
        alert('Función de eliminación en desarrollo para documento ID: ' + id);
    }
}

// Función para convertir fecha dd/mm/yyyy a yyyy-mm-dd
function convertirFechaAFormatoBD(fechaInput) {
    const fecha = fechaInput.value.trim();
    if (!fecha) return true; // Si está vacío, permitir continuar
    
    // Validar formato dd/mm/yyyy
    const fechaRegex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
    const match = fecha.match(fechaRegex);
    
    if (!match) {
        fechaInput.setCustomValidity('Por favor ingrese una fecha válida en formato dd/mm/yyyy');
        fechaInput.classList.add('is-invalid');
        return false;
    }
    
    const dia = parseInt(match[1], 10);
    const mes = parseInt(match[2], 10);
    const anio = parseInt(match[3], 10);
    
    // Validar rango de días y meses
    if (dia < 1 || dia > 31 || mes < 1 || mes > 12) {
        fechaInput.setCustomValidity('Por favor ingrese una fecha válida');
        fechaInput.classList.add('is-invalid');
        return false;
    }
    
    // Convertir a formato yyyy-mm-dd para la base de datos
    const fechaFormatoBD = anio + '-' + String(mes).padStart(2, '0') + '-' + String(dia).padStart(2, '0');
    fechaInput.value = fechaFormatoBD;
    fechaInput.setCustomValidity('');
    fechaInput.classList.remove('is-invalid');
    return true;
}

// Validación del formulario de documento antes de enviar
document.getElementById('formDocumento').addEventListener('submit', function(e) {
    const fechaInput = document.getElementById('fecha_vencimiento');
    const fechaValida = convertirFechaAFormatoBD(fechaInput);
    
    if (!fechaValida && fechaInput.value.trim() !== '') {
        e.preventDefault();
        fechaInput.focus();
        return false;
    }
});

// Restaurar formato dd/mm/yyyy si hay error al enviar
document.getElementById('fecha_vencimiento').addEventListener('input', function() {
    const fecha = this.value;
    // Si el formato es yyyy-mm-dd (desde BD), convertir a dd/mm/yyyy
    const fechaRegexBD = /^(\d{4})-(\d{2})-(\d{2})$/;
    const matchBD = fecha.match(fechaRegexBD);
    if (matchBD) {
        this.value = matchBD[3] + '/' + matchBD[2] + '/' + matchBD[1];
    }
});
</script>

<?php include '../includes/footer.php'; ?>

