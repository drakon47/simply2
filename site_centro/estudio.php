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

// Obtener parámetros
$proyecto_id = $_GET['id'] ?? null;
$centro_id = $user['identidad'];

if (!$proyecto_id) {
    header('Location: index.php');
    exit();
}

// Verificar que el proyecto está asociado al centro y obtener información del proyecto
$stmt = $db->prepare("
    SELECT p.*, l.nombre as laboratorio_nombre, l.pais as laboratorio_pais
    FROM proyectos p 
    JOIN laboratorios l ON p.patrocinante_id = l.id 
    INNER JOIN centros_proyectos cp ON p.id = cp.proyecto_id
    WHERE p.id = ? AND cp.centro_id = ? AND p.activo = 1 AND cp.activo = 1
");
$stmt->execute([$proyecto_id, $centro_id]);
$proyecto = $stmt->fetch();

if (!$proyecto) {
    header('Location: index.php?error=proyecto_no_encontrado');
    exit();
}

// Obtener todos los documentos requeridos del proyecto
$stmt = $db->prepare("SELECT * FROM docu_requerida_proyecto WHERE activo = 1 ORDER BY titulo ASC");
$stmt->execute();
$documentos_requeridos = $stmt->fetchAll();

// Para cada documento, obtener las versiones subidas por este proyecto
$documentos_con_versiones = [];
foreach ($documentos_requeridos as $documento) {
    $stmt = $db->prepare("
        SELECT * FROM documentos_proyecto_subidos 
        WHERE docu_requerida_proyecto_id = ? AND proyecto_id = ? AND activo = 1 
        ORDER BY fecha_subida DESC
    ");
    $stmt->execute([$documento['id'], $proyecto_id]);
    $versiones = $stmt->fetchAll();
    
    $documento['versiones'] = $versiones;
    $documentos_con_versiones[] = $documento;
}

// Configurar título de la página
$pageTitle = 'Información del Estudio: ' . htmlspecialchars($proyecto['nombre']);
$rolColor = '#4FC3F7'; // Azul para CENTRO
include '../includes/header.php';
?>

<!-- Mensajes de éxito y error -->
<?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?php 
        if (isset($_GET['mensaje'])) {
            echo htmlspecialchars($_GET['mensaje']);
        } else {
            echo 'Documento subido exitosamente.';
        }
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error_doc'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($_GET['error_doc']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Mensajes de error -->
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>Proyecto no encontrado o no tiene acceso.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-project-diagram me-2"></i>Información del Estudio: <?php echo htmlspecialchars($proyecto['nombre']); ?>
            </h2>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver al Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Información General del Estudio -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #4FC3F7; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Información General del Estudio
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><i class="fas fa-hashtag me-1"></i>ID del Proyecto:</strong><br>
                        <span class="badge bg-secondary mt-1" style="font-size: 1em; padding: 0.5em;">#<?php echo $proyecto['id']; ?></span>
                    </div>
                    <div class="col-md-4">
                        <strong><i class="fas fa-check-circle me-1"></i>Estado:</strong><br>
                        <?php if ($proyecto['activo']): ?>
                            <span class="badge bg-success mt-1" style="font-size: 1em; padding: 0.5em;">
                                <i class="fas fa-check me-1"></i>Activo
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger mt-1" style="font-size: 1em; padding: 0.5em;">
                                <i class="fas fa-times me-1"></i>Inactivo
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <strong><i class="fas fa-calendar-alt me-1"></i>Fecha de Alta:</strong><br>
                        <p class="mb-0 mt-1">
                            <i class="fas fa-calendar me-1"></i><?php echo date('d/m/Y', strtotime($proyecto['fecha_alta'])); ?><br>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i><?php echo date('H:i:s', strtotime($proyecto['fecha_alta'])); ?>
                            </small>
                        </p>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-12">
                        <strong><i class="fas fa-tag me-1"></i>Nombre del Estudio/Proyecto:</strong><br>
                        <h5 class="mt-2 mb-0" style="color: #4FC3F7;">
                            <?php echo htmlspecialchars($proyecto['nombre']); ?>
                        </h5>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong><i class="fas fa-flask me-1"></i>Patrocinante (Laboratorio):</strong><br>
                        <div class="mt-2">
                            <span class="badge" style="background: #4FC3F7; color: white; font-size: 1em; padding: 0.6em;">
                                <i class="fas fa-building me-1"></i><?php echo htmlspecialchars($proyecto['laboratorio_nombre']); ?>
                            </span>
                            <br>
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-hashtag me-1"></i>ID Patrocinante: <?php echo $proyecto['patrocinante_id']; ?>
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-globe me-1"></i>País del Patrocinante:</strong><br>
                        <p class="mb-0 mt-2">
                            <i class="fas fa-flag me-1"></i><?php echo htmlspecialchars($proyecto['laboratorio_pais']); ?>
                        </p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <strong><i class="fas fa-align-left me-1"></i>Descripción del Estudio:</strong><br>
                        <div class="mt-2 p-3" style="background: #f8f9fa; border-radius: 5px; border-left: 4px solid #4FC3F7;">
                            <p class="mb-0" style="text-align: justify; line-height: 1.8;">
                                <?php if ($proyecto['descripcion']): ?>
                                    <?php echo nl2br(htmlspecialchars($proyecto['descripcion'])); ?>
                                <?php else: ?>
                                    <span class="text-muted"><i>No hay descripción disponible para este estudio.</i></span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Documentación del Estudio -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #4FC3F7; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Documentación del Estudio (<?php echo count($documentos_con_versiones); ?>)
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($documentos_con_versiones)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                        <p>No hay documentos requeridos registrados.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($documentos_con_versiones as $documento): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <i class="fas fa-file-alt me-2 text-primary"></i>
                                        <?php echo htmlspecialchars($documento['titulo']); ?>
                                    </h6>
                                </div>
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalSubirDocumentoProyecto" onclick="abrirModalSubirProyecto(<?php echo $documento['id']; ?>, '<?php echo htmlspecialchars($documento['titulo'], ENT_QUOTES); ?>')">
                                    <i class="fas fa-plus me-1"></i>Subir Documento
                                </button>
                            </div>
                            
                            <!-- Versiones subidas -->
                            <?php if (!empty($documento['versiones'])): ?>
                                <div class="mt-3">
                                    <small class="text-muted d-block mb-2">
                                        <strong>Versiones subidas (<?php echo count($documento['versiones']); ?>):</strong>
                                    </small>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Archivo</th>
                                                    <th>Fecha Subida</th>
                                                    <th>Fecha Vencimiento</th>
                                                    <th>Alerta Roja</th>
                                                    <th>Alerta Amarilla</th>
                                                    <th>Responsable</th>
                                                    <th>Email</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($documento['versiones'] as $version): ?>
                                                <?php
                                                // Calcular estado según fechas
                                                $hoy = strtotime('today');
                                                $fecha_vencimiento = $version['fecha_vencimiento'] ? strtotime($version['fecha_vencimiento']) : null;
                                                $fecha_alerta_roja = $version['fecha_alerta_roja'] ? strtotime($version['fecha_alerta_roja']) : null;
                                                $fecha_alerta_amarilla = $version['fecha_alerta_amarilla'] ? strtotime($version['fecha_alerta_amarilla']) : null;
                                                
                                                $estado = 'vigente';
                                                $badge_class = 'bg-success';
                                                $estado_texto = 'Vigente';
                                                
                                                if ($fecha_vencimiento && $hoy > $fecha_vencimiento) {
                                                    $estado = 'vencido';
                                                    $badge_class = 'bg-danger';
                                                    $estado_texto = 'Vencido';
                                                } elseif ($fecha_alerta_roja && $hoy >= $fecha_alerta_roja) {
                                                    $estado = 'alerta_roja';
                                                    $badge_class = 'bg-danger';
                                                    $estado_texto = 'Alerta Roja';
                                                } elseif ($fecha_alerta_amarilla && $hoy >= $fecha_alerta_amarilla) {
                                                    $estado = 'alerta_amarilla';
                                                    $badge_class = 'bg-warning';
                                                    $estado_texto = 'Alerta Amarilla';
                                                }
                                                ?>
                                                <tr>
                                                    <td>
                                                        <a href="../<?php echo htmlspecialchars($version['ruta_archivo']); ?>" target="_blank" class="text-decoration-none">
                                                            <i class="fas fa-file-download me-1"></i>
                                                            <?php echo htmlspecialchars($version['nombre_archivo']); ?>
                                                        </a>
                                                    </td>
                                                    <td><?php echo date('d/m/Y', strtotime($version['fecha_subida'])); ?></td>
                                                    <td>
                                                        <?php if ($version['fecha_vencimiento']): ?>
                                                            <?php echo date('d/m/Y', strtotime($version['fecha_vencimiento'])); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($version['fecha_alerta_roja']): ?>
                                                            <?php echo date('d/m/Y', strtotime($version['fecha_alerta_roja'])); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($version['fecha_alerta_amarilla']): ?>
                                                            <?php echo date('d/m/Y', strtotime($version['fecha_alerta_amarilla'])); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($version['responsable'] ?: '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($version['email'] ?: '-'); ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $badge_class; ?>"><?php echo $estado_texto; ?></span>
                                                    </td>
                                                    <td>
                                                        <a href="../<?php echo htmlspecialchars($version['ruta_archivo']); ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Descargar">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarDocumentoProyecto(<?php echo $version['id']; ?>, '<?php echo htmlspecialchars($version['nombre_archivo'], ENT_QUOTES); ?>')" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mt-2 mb-0 py-2">
                                    <small><i class="fas fa-info-circle me-1"></i>No hay versiones subidas para este documento.</small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Subir Documento del Proyecto -->
<div class="modal fade" id="modalSubirDocumentoProyecto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #4FC3F7; color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-upload me-2"></i>Subir Documento del Estudio
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSubirDocumentoProyecto" method="POST" action="guardar_documento_proyecto.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="docu_requerida_proyecto_id" id="docu_requerida_proyecto_id">
                    <input type="hidden" name="proyecto_id" value="<?php echo $proyecto_id; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Documento Requerido:</strong></label>
                        <p class="form-control-plaintext" id="titulo_documento_proyecto_mostrar" style="background: #f8f9fa; padding: 0.5rem; border-radius: 0.25rem;"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="archivo_proyecto" class="form-label">Archivo *</label>
                        <input type="file" class="form-control" id="archivo_proyecto" name="archivo" accept=".doc,.docx,.pdf,.xls,.xlsx,.jpg,.jpeg,.png" required>
                        <small class="form-text text-muted">Formatos permitidos: DOC, DOCX, PDF, XLS, XLSX, JPG, JPEG, PNG (Máximo 10MB)</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_vencimiento_proyecto" class="form-label">Fecha de Vencimiento</label>
                            <input type="text" class="form-control" id="fecha_vencimiento_proyecto" name="fecha_vencimiento" placeholder="dd/mm/yyyy" pattern="\d{2}/\d{2}/\d{4}">
                            <small class="form-text text-muted">Formato: dd/mm/yyyy</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_alerta_roja_proyecto" class="form-label">Fecha de Alerta Roja</label>
                            <input type="text" class="form-control" id="fecha_alerta_roja_proyecto" name="fecha_alerta_roja" placeholder="dd/mm/yyyy" pattern="\d{2}/\d{2}/\d{4}">
                            <small class="form-text text-muted">Fecha límite crítica (Formato: dd/mm/yyyy)</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="fecha_alerta_amarilla_proyecto" class="form-label">Fecha de Alerta Amarilla</label>
                        <input type="text" class="form-control" id="fecha_alerta_amarilla_proyecto" name="fecha_alerta_amarilla" placeholder="dd/mm/yyyy" pattern="\d{2}/\d{2}/\d{4}">
                        <small class="form-text text-muted">Fecha de advertencia temprana (Formato: dd/mm/yyyy)</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="responsable_proyecto" class="form-label">Responsable</label>
                            <input type="text" class="form-control" id="responsable_proyecto" name="responsable" placeholder="Nombre del responsable">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email_proyecto" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email_proyecto" name="email" placeholder="email@ejemplo.com">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background: #4FC3F7; color: white; border: none;">
                        <i class="fas fa-upload me-1"></i>Subir Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Configuración de Flatpickr con formato dd/mm/yyyy
const configFecha = {
    dateFormat: 'd/m/Y',
    locale: 'es',
    allowInput: true,
    altInput: false
};

// Variables para almacenar las instancias de flatpickr
let fechaVencimientoProyecto, fechaAlertaRojaProyecto, fechaAlertaAmarillaProyecto;

// Inicializar datepickers cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar datepickers en el modal de documento del proyecto
    fechaVencimientoProyecto = flatpickr('#fecha_vencimiento_proyecto', configFecha);
    fechaAlertaRojaProyecto = flatpickr('#fecha_alerta_roja_proyecto', configFecha);
    fechaAlertaAmarillaProyecto = flatpickr('#fecha_alerta_amarilla_proyecto', configFecha);
});

function abrirModalSubirProyecto(docuId, titulo) {
    document.getElementById('docu_requerida_proyecto_id').value = docuId;
    document.getElementById('titulo_documento_proyecto_mostrar').textContent = titulo;
    // Limpiar formulario
    document.getElementById('formSubirDocumentoProyecto').reset();
    document.getElementById('docu_requerida_proyecto_id').value = docuId;
    document.getElementById('titulo_documento_proyecto_mostrar').textContent = titulo;
    // Limpiar fechas en los datepickers
    if (fechaVencimientoProyecto) fechaVencimientoProyecto.clear();
    if (fechaAlertaRojaProyecto) fechaAlertaRojaProyecto.clear();
    if (fechaAlertaAmarillaProyecto) fechaAlertaAmarillaProyecto.clear();
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

// Validar tamaño de archivo y fechas antes de enviar
document.getElementById('formSubirDocumentoProyecto').addEventListener('submit', function(e) {
    const archivo = document.getElementById('archivo_proyecto').files[0];
    if (archivo) {
        const tamañoMaximo = 10 * 1024 * 1024; // 10MB
        if (archivo.size > tamañoMaximo) {
            e.preventDefault();
            alert('El archivo es demasiado grande. Tamaño máximo: 10MB');
            return false;
        }
    }
    
    // Validar y convertir fechas
    const fechaVencimiento = document.getElementById('fecha_vencimiento_proyecto');
    const fechaAlertaRoja = document.getElementById('fecha_alerta_roja_proyecto');
    const fechaAlertaAmarilla = document.getElementById('fecha_alerta_amarilla_proyecto');
    
    const fechaVencimientoValida = convertirFechaAFormatoBD(fechaVencimiento);
    const fechaAlertaRojaValida = convertirFechaAFormatoBD(fechaAlertaRoja);
    const fechaAlertaAmarillaValida = convertirFechaAFormatoBD(fechaAlertaAmarilla);
    
    if ((fechaVencimiento.value.trim() !== '' && !fechaVencimientoValida) ||
        (fechaAlertaRoja.value.trim() !== '' && !fechaAlertaRojaValida) ||
        (fechaAlertaAmarilla.value.trim() !== '' && !fechaAlertaAmarillaValida)) {
        e.preventDefault();
        return false;
    }
});

// Restaurar formato dd/mm/yyyy si hay error al enviar
document.getElementById('fecha_vencimiento_proyecto').addEventListener('input', function() {
    const fecha = this.value;
    // Si el formato es yyyy-mm-dd (desde BD), convertir a dd/mm/yyyy
    const fechaRegexBD = /^(\d{4})-(\d{2})-(\d{2})$/;
    const matchBD = fecha.match(fechaRegexBD);
    if (matchBD) {
        this.value = matchBD[3] + '/' + matchBD[2] + '/' + matchBD[1];
    }
    this.classList.remove('is-invalid');
});

document.getElementById('fecha_alerta_roja_proyecto').addEventListener('input', function() {
    const fecha = this.value;
    const fechaRegexBD = /^(\d{4})-(\d{2})-(\d{2})$/;
    const matchBD = fecha.match(fechaRegexBD);
    if (matchBD) {
        this.value = matchBD[3] + '/' + matchBD[2] + '/' + matchBD[1];
    }
    this.classList.remove('is-invalid');
});

document.getElementById('fecha_alerta_amarilla_proyecto').addEventListener('input', function() {
    const fecha = this.value;
    const fechaRegexBD = /^(\d{4})-(\d{2})-(\d{2})$/;
    const matchBD = fecha.match(fechaRegexBD);
    if (matchBD) {
        this.value = matchBD[3] + '/' + matchBD[2] + '/' + matchBD[1];
    }
    this.classList.remove('is-invalid');
});

// Función para eliminar documento del proyecto
function eliminarDocumentoProyecto(id, nombreArchivo) {
    if (confirm('¿Estás seguro de que quieres eliminar el documento "' + nombreArchivo + '"?\n\nEsta acción eliminará el archivo y el registro. No se puede deshacer.')) {
        window.location.href = 'eliminar_documento_proyecto.php?id=' + id + '&proyecto_id=<?php echo $proyecto_id; ?>';
    }
}
</script>

<?php include '../includes/footer.php'; ?>

