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
    SELECT p.*, pr.nombre as proyecto_nombre
    FROM pacientes p
    LEFT JOIN proyectos pr ON p.proyecto_id = pr.id
    WHERE p.id = ? AND p.centro_id = ? AND p.activo = 1
");
$stmt->execute([$paciente_id, $user['identidad']]);
$paciente = $stmt->fetch();

if (!$paciente) {
    header('Location: pacientes.php?error=4');
    exit();
}

// Obtener proyectos asociados al centro para el formulario
$stmt = $db->prepare("
    SELECT p.* 
    FROM proyectos p 
    INNER JOIN centros_proyectos cp ON p.id = cp.proyecto_id 
    WHERE cp.centro_id = ? AND cp.activo = 1 AND p.activo = 1
    ORDER BY p.nombre
");
$stmt->execute([$user['identidad']]);
$proyectos = $stmt->fetchAll();
?>

<?php
$pageTitle = 'Editar Paciente';
$rolColor = '#4FC3F7'; // Azul de la imagen
include '../includes/header.php';
?>

<!-- Mensajes de éxito y error -->
<?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>Paciente actualizado exitosamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <?php if ($_GET['error'] == '1'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Error al actualizar el paciente. Intente nuevamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($_GET['error'] == '2'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>El proyecto seleccionado no está asociado a este centro.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($_GET['error'] == '3'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>El formato del email no es válido. Por favor ingrese un email correcto.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($_GET['error'] == '4'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Paciente no encontrado o no tiene permisos para editarlo.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-edit me-2"></i>Editar Paciente: <?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']); ?>
            </h2>
            <div>
                <a href="ver_paciente.php?id=<?php echo $paciente['id']; ?>" class="btn btn-secondary me-2">
                    <i class="fas fa-eye me-1"></i>Ver
                </a>
                <a href="pacientes.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: #4FC3F7; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i>Formulario de Edición
                </h5>
            </div>
            <div class="card-body">
                <form id="formPaciente" method="POST" action="guardar_edicion_paciente.php">
                    <input type="hidden" name="paciente_id" value="<?php echo $paciente['id']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Datos Personales</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre *</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($paciente['nombre']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="apellido" class="form-label">Apellido *</label>
                                        <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($paciente['apellido']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($paciente['email']); ?>" onblur="validarEmail(this)">
                                <div class="invalid-feedback" id="email-error">
                                    Por favor ingrese un email válido.
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telefono1" class="form-label">Teléfono de Contacto 1</label>
                                        <input type="tel" class="form-control" id="telefono1" name="telefono1" value="<?php echo htmlspecialchars($paciente['telefono1']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telefono2" class="form-label">Teléfono de Contacto 2</label>
                                        <input type="tel" class="form-control" id="telefono2" name="telefono2" value="<?php echo htmlspecialchars($paciente['telefono2']); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Domicilio</h6>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="domicilio_calle" class="form-label">Calle</label>
                                        <input type="text" class="form-control" id="domicilio_calle" name="domicilio_calle" value="<?php echo htmlspecialchars($paciente['domicilio_calle']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="domicilio_numero" class="form-label">Número</label>
                                        <input type="text" class="form-control" id="domicilio_numero" name="domicilio_numero" value="<?php echo htmlspecialchars($paciente['domicilio_numero']); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="domicilio_piso" class="form-label">Piso</label>
                                        <input type="text" class="form-control" id="domicilio_piso" name="domicilio_piso" value="<?php echo htmlspecialchars($paciente['domicilio_piso']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="domicilio_depto" class="form-label">Depto</label>
                                        <input type="text" class="form-control" id="domicilio_depto" name="domicilio_depto" value="<?php echo htmlspecialchars($paciente['domicilio_depto']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="domicilio_localidad" class="form-label">Localidad</label>
                                        <input type="text" class="form-control" id="domicilio_localidad" name="domicilio_localidad" value="<?php echo htmlspecialchars($paciente['domicilio_localidad']); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="domicilio_provincia" class="form-label">Provincia</label>
                                <input type="text" class="form-control" id="domicilio_provincia" name="domicilio_provincia" value="<?php echo htmlspecialchars($paciente['domicilio_provincia']); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Contacto de Emergencia</h6>
                            <div class="mb-3">
                                <label for="familiar_contacto" class="form-label">Familiar de Contacto</label>
                                <input type="text" class="form-control" id="familiar_contacto" name="familiar_contacto" value="<?php echo htmlspecialchars($paciente['familiar_contacto']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="telefono_familiar" class="form-label">Teléfono del Familiar</label>
                                <input type="tel" class="form-control" id="telefono_familiar" name="telefono_familiar" value="<?php echo htmlspecialchars($paciente['telefono_familiar']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Información del Estudio</h6>
                            <div class="mb-3">
                                <label for="proyecto_id" class="form-label">Proyecto/Estudio</label>
                                <?php if (empty($proyectos)): ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        No hay proyectos asociados a este centro. Contacte al administrador para asociar proyectos.
                                    </div>
                                    <input type="hidden" name="proyecto_id" value="<?php echo $paciente['proyecto_id']; ?>">
                                <?php else: ?>
                                    <select class="form-select" id="proyecto_id" name="proyecto_id">
                                        <option value="">Seleccionar proyecto</option>
                                        <?php foreach ($proyectos as $proyecto): ?>
                                            <option value="<?php echo $proyecto['id']; ?>" <?php echo ($proyecto['id'] == $paciente['proyecto_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($proyecto['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="consentimiento_firmado" class="form-label">Consentimiento Firmado</label>
                                <select class="form-select" id="consentimiento_firmado" name="consentimiento_firmado">
                                    <option value="NO" <?php echo ($paciente['consentimiento_firmado'] == 'NO') ? 'selected' : ''; ?>>NO</option>
                                    <option value="SI" <?php echo ($paciente['consentimiento_firmado'] == 'SI') ? 'selected' : ''; ?>>SI</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="fecha_alta" class="form-label">Fecha de Alta</label>
                                <input type="text" class="form-control" id="fecha_alta" name="fecha_alta" placeholder="dd/mm/yyyy" pattern="\d{2}/\d{2}/\d{4}" value="<?php echo date('d/m/Y', strtotime($paciente['fecha_alta'])); ?>">
                                <small class="form-text text-muted">Fecha de registro del paciente en el sistema (Formato: dd/mm/yyyy)</small>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="comentarios" class="form-label">Comentarios</label>
                        <textarea class="form-control" id="comentarios" name="comentarios" rows="3"><?php echo htmlspecialchars($paciente['comentarios']); ?></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <a href="ver_paciente.php?id=<?php echo $paciente['id']; ?>" class="btn btn-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn" style="background: #4FC3F7; color: white; border: none;">
                            <i class="fas fa-save me-1"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
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
    
    // Inicializar datepicker para fecha de alta
    flatpickr('#fecha_alta', configFecha);
});

function validarEmail(input) {
    const email = input.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email === '') {
        // Si está vacío, no mostrar error (es opcional)
        input.classList.remove('is-invalid');
        input.classList.remove('is-valid');
        return true;
    }
    
    if (emailRegex.test(email)) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        return true;
    } else {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        return false;
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

// Validación del formulario antes de enviar
document.getElementById('formPaciente').addEventListener('submit', function(e) {
    const emailInput = document.getElementById('email');
    const emailValido = validarEmail(emailInput);
    const fechaInput = document.getElementById('fecha_alta');
    const fechaValida = convertirFechaAFormatoBD(fechaInput);
    
    if (!emailValido && emailInput.value.trim() !== '') {
        e.preventDefault();
        emailInput.focus();
        return false;
    }
    
    if (!fechaValida && fechaInput.value.trim() !== '') {
        e.preventDefault();
        fechaInput.focus();
        return false;
    }
});

// Restaurar formato dd/mm/yyyy si hay error al enviar
document.getElementById('fecha_alta').addEventListener('input', function() {
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
