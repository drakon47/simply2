<?php
$pageTitle = 'Centro - Documentación del Centro';
$rolColor = '#4FC3F7'; // Azul de la imagen
include '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Obtener información del usuario y centro
$user = $auth->getUserInfo();
$centro_id = $user['identidad'];

// Obtener todos los documentos requeridos del centro
$stmt = $db->prepare("SELECT * FROM docu_requerida_centro WHERE activo = 1 ORDER BY titulo ASC");
$stmt->execute();
$documentos = $stmt->fetchAll();

// Para cada documento, obtener las versiones subidas por este centro
$documentos_con_versiones = [];
foreach ($documentos as $documento) {
    $stmt = $db->prepare("
        SELECT * FROM documentos_centro_subidos 
        WHERE docu_requerida_centro_id = ? AND centro_id = ? AND activo = 1 
        ORDER BY fecha_subida DESC
    ");
    $stmt->execute([$documento['id'], $centro_id]);
    $versiones = $stmt->fetchAll();
    
    $documento['versiones'] = $versiones;
    $documentos_con_versiones[] = $documento;
}

// Obtener documentos adicionales del centro
$stmt = $db->prepare("
    SELECT * FROM documentos_centro_adicional 
    WHERE centro_id = ? AND activo = 1 
    ORDER BY fecha_subida DESC
");
$stmt->execute([$centro_id]);
$documentos_adicionales = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-file-alt me-2"></i>Documentación del Centro
            </h2>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver al Dashboard
            </a>
        </div>
    </div>
</div>

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

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Lista de Documentos Requeridos -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #4FC3F7; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Documentos Requeridos (<?php echo count($documentos_con_versiones); ?>)
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
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalSubirDocumento" onclick="abrirModalSubir(<?php echo $documento['id']; ?>, '<?php echo htmlspecialchars($documento['titulo'], ENT_QUOTES); ?>')">
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
                                                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarDocumento(<?php echo $version['id']; ?>, '<?php echo htmlspecialchars($version['nombre_archivo'], ENT_QUOTES); ?>')" title="Eliminar">
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

<!-- Documentación Adicional -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #4FC3F7; color: white;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="fas fa-folder-plus me-2"></i>Documentación Adicional
                        </h5>
                        <small class="d-block mt-1" style="opacity: 0.9;">Consigne aquí documentos requeridos por su jurisdicción o documentos adicionales</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#modalSubirDocumentoAdicional">
                        <i class="fas fa-plus me-1"></i>Subir Documento Adicional
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($documentos_adicionales)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-folder-open fa-3x mb-3"></i>
                        <p>No hay documentación adicional subida.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Descripción</th>
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
                                <?php foreach ($documentos_adicionales as $doc_adicional): ?>
                                <?php
                                // Calcular estado según fechas
                                $hoy = strtotime('today');
                                $fecha_vencimiento = $doc_adicional['fecha_vencimiento'] ? strtotime($doc_adicional['fecha_vencimiento']) : null;
                                $fecha_alerta_roja = $doc_adicional['fecha_alerta_roja'] ? strtotime($doc_adicional['fecha_alerta_roja']) : null;
                                $fecha_alerta_amarilla = $doc_adicional['fecha_alerta_amarilla'] ? strtotime($doc_adicional['fecha_alerta_amarilla']) : null;
                                
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
                                    <td><strong><?php echo htmlspecialchars($doc_adicional['descripcion']); ?></strong></td>
                                    <td>
                                        <a href="../<?php echo htmlspecialchars($doc_adicional['ruta_archivo']); ?>" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-file-download me-1"></i>
                                            <?php echo htmlspecialchars($doc_adicional['nombre_archivo']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($doc_adicional['fecha_subida'])); ?></td>
                                    <td>
                                        <?php if ($doc_adicional['fecha_vencimiento']): ?>
                                            <?php echo date('d/m/Y', strtotime($doc_adicional['fecha_vencimiento'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($doc_adicional['fecha_alerta_roja']): ?>
                                            <?php echo date('d/m/Y', strtotime($doc_adicional['fecha_alerta_roja'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($doc_adicional['fecha_alerta_amarilla']): ?>
                                            <?php echo date('d/m/Y', strtotime($doc_adicional['fecha_alerta_amarilla'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($doc_adicional['responsable'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($doc_adicional['email'] ?: '-'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo $estado_texto; ?></span>
                                    </td>
                                    <td>
                                        <a href="../<?php echo htmlspecialchars($doc_adicional['ruta_archivo']); ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Descargar">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarDocumentoAdicional(<?php echo $doc_adicional['id']; ?>, '<?php echo htmlspecialchars($doc_adicional['nombre_archivo'], ENT_QUOTES); ?>')" title="Eliminar">
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

<!-- Modal para Subir Documento -->
<div class="modal fade" id="modalSubirDocumento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #4FC3F7; color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-upload me-2"></i>Subir Documento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSubirDocumento" method="POST" action="guardar_documento_centro.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="docu_requerida_centro_id" id="docu_requerida_centro_id">
                    <input type="hidden" name="centro_id" value="<?php echo $centro_id; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Documento Requerido:</strong></label>
                        <p class="form-control-plaintext" id="titulo_documento_mostrar" style="background: #f8f9fa; padding: 0.5rem; border-radius: 0.25rem;"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="archivo" class="form-label">Archivo *</label>
                        <input type="file" class="form-control" id="archivo" name="archivo" accept=".doc,.docx,.pdf,.xls,.xlsx,.jpg,.jpeg,.png" required>
                        <small class="form-text text-muted">Formatos permitidos: DOC, DOCX, PDF, XLS, XLSX, JPG, JPEG, PNG (Máximo 10MB)</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
                            <input type="text" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" placeholder="dd/mm/yyyy" pattern="\d{2}/\d{2}/\d{4}">
                            <small class="form-text text-muted">Formato: dd/mm/yyyy</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_alerta_roja" class="form-label">Fecha de Alerta Roja</label>
                            <input type="text" class="form-control" id="fecha_alerta_roja" name="fecha_alerta_roja" placeholder="dd/mm/yyyy" pattern="\d{2}/\d{2}/\d{4}">
                            <small class="form-text text-muted">Fecha límite crítica (Formato: dd/mm/yyyy)</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="fecha_alerta_amarilla" class="form-label">Fecha de Alerta Amarilla</label>
                        <input type="text" class="form-control" id="fecha_alerta_amarilla" name="fecha_alerta_amarilla" placeholder="dd/mm/yyyy" pattern="\d{2}/\d{2}/\d{4}">
                        <small class="form-text text-muted">Fecha de advertencia temprana (Formato: dd/mm/yyyy)</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="responsable" class="form-label">Responsable</label>
                            <input type="text" class="form-control" id="responsable" name="responsable" placeholder="Nombre del responsable">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="email@ejemplo.com">
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
let fechaVencimiento, fechaAlertaRoja, fechaAlertaAmarilla;
let fechaVencimientoAdicional, fechaAlertaRojaAdicional, fechaAlertaAmarillaAdicional;

// Inicializar datepickers cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar datepickers en el modal de documento requerido
    fechaVencimiento = flatpickr('#fecha_vencimiento', configFecha);
    fechaAlertaRoja = flatpickr('#fecha_alerta_roja', configFecha);
    fechaAlertaAmarilla = flatpickr('#fecha_alerta_amarilla', configFecha);
    
    // Inicializar datepickers en el modal de documento adicional
    fechaVencimientoAdicional = flatpickr('#fecha_vencimiento_adicional', configFecha);
    fechaAlertaRojaAdicional = flatpickr('#fecha_alerta_roja_adicional', configFecha);
    fechaAlertaAmarillaAdicional = flatpickr('#fecha_alerta_amarilla_adicional', configFecha);
});

function abrirModalSubir(docuId, titulo) {
    document.getElementById('docu_requerida_centro_id').value = docuId;
    document.getElementById('titulo_documento_mostrar').textContent = titulo;
    // Limpiar formulario
    document.getElementById('formSubirDocumento').reset();
    document.getElementById('docu_requerida_centro_id').value = docuId;
    document.getElementById('titulo_documento_mostrar').textContent = titulo;
    // Limpiar fechas en los datepickers
    if (fechaVencimiento) fechaVencimiento.clear();
    if (fechaAlertaRoja) fechaAlertaRoja.clear();
    if (fechaAlertaAmarilla) fechaAlertaAmarilla.clear();
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
document.getElementById('formSubirDocumento').addEventListener('submit', function(e) {
    const archivo = document.getElementById('archivo').files[0];
    if (archivo) {
        const tamañoMaximo = 10 * 1024 * 1024; // 10MB
        if (archivo.size > tamañoMaximo) {
            e.preventDefault();
            alert('El archivo es demasiado grande. Tamaño máximo: 10MB');
            return false;
        }
    }
    
    // Validar y convertir fechas
    const fechaVencimiento = document.getElementById('fecha_vencimiento');
    const fechaAlertaRoja = document.getElementById('fecha_alerta_roja');
    const fechaAlertaAmarilla = document.getElementById('fecha_alerta_amarilla');
    
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
document.getElementById('fecha_vencimiento').addEventListener('input', function() {
    const fecha = this.value;
    // Si el formato es yyyy-mm-dd (desde BD), convertir a dd/mm/yyyy
    const fechaRegexBD = /^(\d{4})-(\d{2})-(\d{2})$/;
    const matchBD = fecha.match(fechaRegexBD);
    if (matchBD) {
        this.value = matchBD[3] + '/' + matchBD[2] + '/' + matchBD[1];
    }
    this.classList.remove('is-invalid');
});

document.getElementById('fecha_alerta_roja').addEventListener('input', function() {
    const fecha = this.value;
    const fechaRegexBD = /^(\d{4})-(\d{2})-(\d{2})$/;
    const matchBD = fecha.match(fechaRegexBD);
    if (matchBD) {
        this.value = matchBD[3] + '/' + matchBD[2] + '/' + matchBD[1];
    }
    this.classList.remove('is-invalid');
});

document.getElementById('fecha_alerta_amarilla').addEventListener('input', function() {
    const fecha = this.value;
    const fechaRegexBD = /^(\d{4})-(\d{2})-(\d{2})$/;
    const matchBD = fecha.match(fechaRegexBD);
    if (matchBD) {
        this.value = matchBD[3] + '/' + matchBD[2] + '/' + matchBD[1];
    }
    this.classList.remove('is-invalid');
});

// Función para eliminar documento
function eliminarDocumento(id, nombreArchivo) {
    if (confirm('¿Estás seguro de que quieres eliminar el documento "' + nombreArchivo + '"?\n\nEsta acción eliminará el archivo y el registro. No se puede deshacer.')) {
        window.location.href = 'eliminar_documento_centro.php?id=' + id;
    }
}

// Función para eliminar documento adicional
function eliminarDocumentoAdicional(id, nombreArchivo) {
    if (confirm('¿Estás seguro de que quieres eliminar el documento "' + nombreArchivo + '"?\n\nEsta acción eliminará el archivo y el registro. No se puede deshacer.')) {
        window.location.href = 'eliminar_documento_adicional.php?id=' + id;
    }
}
</script>

<!-- Modal para Subir Documento Adicional -->
<div class="modal fade" id="modalSubirDocumentoAdicional" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #4FC3F7; color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-upload me-2"></i>Subir Documento Adicional
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSubirDocumentoAdicional" method="POST" action="guardar_documento_adicional.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="centro_id" value="<?php echo $centro_id; ?>">
                    
                    <div class="mb-3">
                        <label for="descripcion_adicional" class="form-label">Descripción *</label>
                        <input type="text" class="form-control" id="descripcion_adicional" name="descripcion" required placeholder="Ej: Certificado de capacitación, Contrato adicional, etc.">
                        <small class="form-text text-muted">Describe brevemente el documento</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="archivo_adicional" class="form-label">Archivo *</label>
                        <input type="file" class="form-control" id="archivo_adicional" name="archivo" accept=".doc,.docx,.pdf,.xls,.xlsx,.jpg,.jpeg,.png" required>
                        <small class="form-text text-muted">Formatos permitidos: DOC, DOCX, PDF, XLS, XLSX, JPG, JPEG, PNG (Máximo 10MB)</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_vencimiento_adicional" class="form-label">Fecha de Vencimiento</label>
                            <input type="text" class="form-control" id="fecha_vencimiento_adicional" name="fecha_vencimiento" placeholder="dd/mm/yyyy" pattern="\d{2}/\d{2}/\d{4}">
                            <small class="form-text text-muted">Formato: dd/mm/yyyy</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_alerta_roja_adicional" class="form-label">Fecha de Alerta Roja</label>
                            <input type="text" class="form-control" id="fecha_alerta_roja_adicional" name="fecha_alerta_roja" placeholder="dd/mm/yyyy" pattern="\d{2}/\d{2}/\d{4}">
                            <small class="form-text text-muted">Fecha límite crítica (Formato: dd/mm/yyyy)</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="fecha_alerta_amarilla_adicional" class="form-label">Fecha de Alerta Amarilla</label>
                        <input type="text" class="form-control" id="fecha_alerta_amarilla_adicional" name="fecha_alerta_amarilla" placeholder="dd/mm/yyyy" pattern="\d{2}/\d{2}/\d{4}">
                        <small class="form-text text-muted">Fecha de advertencia temprana (Formato: dd/mm/yyyy)</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="responsable_adicional" class="form-label">Responsable</label>
                            <input type="text" class="form-control" id="responsable_adicional" name="responsable" placeholder="Nombre del responsable">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email_adicional" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email_adicional" name="email" placeholder="email@ejemplo.com">
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
// Validar tamaño de archivo y fechas antes de enviar (documento adicional)
document.getElementById('formSubirDocumentoAdicional').addEventListener('submit', function(e) {
    const archivo = document.getElementById('archivo_adicional').files[0];
    if (archivo) {
        const tamañoMaximo = 10 * 1024 * 1024; // 10MB
        if (archivo.size > tamañoMaximo) {
            e.preventDefault();
            alert('El archivo es demasiado grande. Tamaño máximo: 10MB');
            return false;
        }
    }
    
    // Validar y convertir fechas
    const fechaVencimiento = document.getElementById('fecha_vencimiento_adicional');
    const fechaAlertaRoja = document.getElementById('fecha_alerta_roja_adicional');
    const fechaAlertaAmarilla = document.getElementById('fecha_alerta_amarilla_adicional');
    
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

// Restaurar formato dd/mm/yyyy si hay error al enviar (documento adicional)
document.getElementById('fecha_vencimiento_adicional').addEventListener('input', function() {
    const fecha = this.value;
    const fechaRegexBD = /^(\d{4})-(\d{2})-(\d{2})$/;
    const matchBD = fecha.match(fechaRegexBD);
    if (matchBD) {
        this.value = matchBD[3] + '/' + matchBD[2] + '/' + matchBD[1];
    }
    this.classList.remove('is-invalid');
});

document.getElementById('fecha_alerta_roja_adicional').addEventListener('input', function() {
    const fecha = this.value;
    const fechaRegexBD = /^(\d{4})-(\d{2})-(\d{2})$/;
    const matchBD = fecha.match(fechaRegexBD);
    if (matchBD) {
        this.value = matchBD[3] + '/' + matchBD[2] + '/' + matchBD[1];
    }
    this.classList.remove('is-invalid');
});

document.getElementById('fecha_alerta_amarilla_adicional').addEventListener('input', function() {
    const fecha = this.value;
    const fechaRegexBD = /^(\d{4})-(\d{2})-(\d{2})$/;
    const matchBD = fecha.match(fechaRegexBD);
    if (matchBD) {
        this.value = matchBD[3] + '/' + matchBD[2] + '/' + matchBD[1];
    }
    this.classList.remove('is-invalid');
});
</script>

<?php include '../includes/footer.php'; ?>

