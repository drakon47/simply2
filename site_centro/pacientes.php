<?php
$pageTitle = 'Gestión de Pacientes';
$rolColor = '#4FC3F7'; // Azul de la imagen
include '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Obtener el ID del centro del usuario logueado
$user = $auth->getUserInfo();
$centro_id = $user['identidad'];

// Verificar si se está filtrando por proyecto específico
$proyecto_filtro = $_GET['proyecto'] ?? null;

if ($proyecto_filtro) {
    // Obtener pacientes del centro filtrados por proyecto
    $stmt = $db->prepare("
        SELECT p.*, pr.nombre as proyecto_nombre, pr.descripcion as proyecto_descripcion
        FROM pacientes p
        LEFT JOIN proyectos pr ON p.proyecto_id = pr.id
        WHERE p.centro_id = ? AND p.proyecto_id = ? AND p.activo = 1
        ORDER BY p.fecha_alta DESC
    ");
    $stmt->execute([$centro_id, $proyecto_filtro]);
    $pacientes = $stmt->fetchAll();
    
    // Obtener nombre del proyecto para mostrar en el título
    $stmt = $db->prepare("SELECT nombre FROM proyectos WHERE id = ?");
    $stmt->execute([$proyecto_filtro]);
    $proyecto_nombre = $stmt->fetch()['nombre'] ?? 'Proyecto';
} else {
    // Obtener todos los pacientes del centro
    $stmt = $db->prepare("
        SELECT p.*, pr.nombre as proyecto_nombre, pr.descripcion as proyecto_descripcion
        FROM pacientes p
        LEFT JOIN proyectos pr ON p.proyecto_id = pr.id
        WHERE p.centro_id = ? AND p.activo = 1
        ORDER BY p.fecha_alta DESC
    ");
    $stmt->execute([$centro_id]);
    $pacientes = $stmt->fetchAll();
    $proyecto_nombre = null;
}

// Obtener proyectos asociados al centro para el formulario
$stmt = $db->prepare("
    SELECT p.* 
    FROM proyectos p 
    INNER JOIN centros_proyectos cp ON p.id = cp.proyecto_id 
    WHERE cp.centro_id = ? AND cp.activo = 1 AND p.activo = 1
    ORDER BY p.nombre
");
$stmt->execute([$centro_id]);
$proyectos = $stmt->fetchAll();
?>

<!-- Mensajes de éxito y error -->
<?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>Paciente registrado exitosamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <?php if ($_GET['error'] == '1'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Error al registrar el paciente. Intente nuevamente.
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
    <?php endif; ?>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-user-injured me-2"></i>
                <?php if ($proyecto_nombre): ?>
                    Pacientes del Proyecto: <?php echo htmlspecialchars($proyecto_nombre); ?>
                <?php else: ?>
                    Gestión de Pacientes
                <?php endif; ?>
            </h2>
            <div>
                <?php if ($proyecto_nombre): ?>
                    <a href="pacientes.php" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>Ver Todos
                    </a>
                <?php endif; ?>
                <button class="btn" style="background: #4FC3F7; color: white; border: none;" data-bs-toggle="modal" data-bs-target="#nuevoPacienteModal">
                    <i class="fas fa-plus me-1"></i>Alta de Paciente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="input-group">
            <span class="input-group-text" style="background: #4FC3F7; color: white;"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" id="searchInput" placeholder="Buscar pacientes...">
        </div>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="filterProyecto">
            <option value="">Todos los proyectos</option>
            <?php foreach ($proyectos as $proyecto): ?>
                <option value="<?php echo $proyecto['id']; ?>"><?php echo htmlspecialchars($proyecto['nombre']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="filterConsentimiento">
            <option value="">Todos los consentimientos</option>
            <option value="SI">Consentimiento firmado</option>
            <option value="NO">Sin consentimiento</option>
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="sortBy">
            <option value="fecha_alta_desc">Más recientes</option>
            <option value="fecha_alta_asc">Más antiguos</option>
            <option value="nombre_asc">Nombre A-Z</option>
            <option value="nombre_desc">Nombre Z-A</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="pacientesTable">
                        <thead style="background: #4FC3F7; color: white;">
                            <tr>
                                <th onclick="sortTable(0)">ID <i class="fas fa-sort"></i></th>
                                <th onclick="sortTable(1)">Nombre <i class="fas fa-sort"></i></th>
                                <th onclick="sortTable(2)">Apellido <i class="fas fa-sort"></i></th>
                                <th onclick="sortTable(3)">Email <i class="fas fa-sort"></i></th>
                                <th onclick="sortTable(4)">Teléfono <i class="fas fa-sort"></i></th>
                                <th onclick="sortTable(5)">Proyecto <i class="fas fa-sort"></i></th>
                                <th onclick="sortTable(6)">Consentimiento <i class="fas fa-sort"></i></th>
                                <th onclick="sortTable(7)">Fecha Alta <i class="fas fa-sort"></i></th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pacientes as $paciente): ?>
                            <tr>
                                <td><?php echo $paciente['id']; ?></td>
                                <td><?php echo htmlspecialchars($paciente['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($paciente['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($paciente['email']); ?></td>
                                <td><?php echo htmlspecialchars($paciente['telefono1']); ?></td>
                                <td>
                                    <?php if ($paciente['proyecto_nombre']): ?>
                                        <span class="badge" style="background: #4FC3F7; color: white;">
                                            <?php echo htmlspecialchars($paciente['proyecto_nombre']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Sin proyecto</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($paciente['consentimiento_firmado'] == 'SI'): ?>
                                        <span class="badge bg-success">Firmado</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($paciente['fecha_alta'])); ?></td>
                                <td>
                                    <button class="btn btn-sm" style="background: #4FC3F7; color: white; border: none;" onclick="verPaciente(<?php echo $paciente['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="editarPaciente(<?php echo $paciente['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarPaciente(<?php echo $paciente['id']; ?>)">
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

<!-- Modal Nuevo Paciente -->
<div class="modal fade" id="nuevoPacienteModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: #4FC3F7; color: white;">
                <h5 class="modal-title">Alta de Paciente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPaciente" method="POST" action="guardar_paciente.php">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Datos Personales</h6>
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
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" onblur="validarEmail(this)">
                                <div class="invalid-feedback" id="email-error">
                                    Por favor ingrese un email válido.
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telefono1" class="form-label">Teléfono de Contacto 1</label>
                                        <input type="tel" class="form-control" id="telefono1" name="telefono1">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telefono2" class="form-label">Teléfono de Contacto 2</label>
                                        <input type="tel" class="form-control" id="telefono2" name="telefono2">
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
                                        <input type="text" class="form-control" id="domicilio_calle" name="domicilio_calle">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="domicilio_numero" class="form-label">Número</label>
                                        <input type="text" class="form-control" id="domicilio_numero" name="domicilio_numero">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="domicilio_piso" class="form-label">Piso</label>
                                        <input type="text" class="form-control" id="domicilio_piso" name="domicilio_piso">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="domicilio_depto" class="form-label">Depto</label>
                                        <input type="text" class="form-control" id="domicilio_depto" name="domicilio_depto">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="domicilio_localidad" class="form-label">Localidad</label>
                                        <input type="text" class="form-control" id="domicilio_localidad" name="domicilio_localidad">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="domicilio_provincia" class="form-label">Provincia</label>
                                <input type="text" class="form-control" id="domicilio_provincia" name="domicilio_provincia">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Contacto de Emergencia</h6>
                            <div class="mb-3">
                                <label for="familiar_contacto" class="form-label">Familiar de Contacto</label>
                                <input type="text" class="form-control" id="familiar_contacto" name="familiar_contacto">
                            </div>
                            <div class="mb-3">
                                <label for="telefono_familiar" class="form-label">Teléfono del Familiar</label>
                                <input type="tel" class="form-control" id="telefono_familiar" name="telefono_familiar">
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
                                    <input type="hidden" name="proyecto_id" value="">
                                <?php else: ?>
                                    <select class="form-select" id="proyecto_id" name="proyecto_id">
                                        <option value="">Seleccionar proyecto</option>
                                        <?php foreach ($proyectos as $proyecto): ?>
                                            <option value="<?php echo $proyecto['id']; ?>"><?php echo htmlspecialchars($proyecto['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="consentimiento_firmado" class="form-label">Consentimiento Firmado</label>
                                <select class="form-select" id="consentimiento_firmado" name="consentimiento_firmado">
                                    <option value="NO" selected>NO</option>
                                    <option value="SI">SI</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="fecha_alta" class="form-label">Fecha de Alta</label>
                                <input type="text" class="form-control" id="fecha_alta" name="fecha_alta" placeholder="dd/mm/yyyy" pattern="\d{2}/\d{2}/\d{4}" value="<?php echo date('d/m/Y'); ?>">
                                <small class="form-text text-muted">Fecha de registro del paciente en el sistema (Formato: dd/mm/yyyy)</small>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="comentarios" class="form-label">Comentarios</label>
                        <textarea class="form-control" id="comentarios" name="comentarios" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background: #4FC3F7; color: white; border: none;">Guardar Paciente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Inicializar datepickers cuando el DOM esté listo
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
    
    // Filtros y búsqueda
    const searchInput = document.getElementById('searchInput');
    const filterProyecto = document.getElementById('filterProyecto');
    const filterConsentimiento = document.getElementById('filterConsentimiento');
    const sortBy = document.getElementById('sortBy');
    const table = document.getElementById('pacientesTable');
    const rows = table.querySelectorAll('tbody tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedProyecto = filterProyecto.value;
        const selectedConsentimiento = filterConsentimiento.value;
        const sortValue = sortBy.value;

        let visibleRows = [];

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const nombre = cells[1].textContent.toLowerCase();
            const apellido = cells[2].textContent.toLowerCase();
            const email = cells[3].textContent.toLowerCase();
            const proyecto = cells[5].textContent;
            const consentimiento = cells[6].textContent;

            const matchesSearch = nombre.includes(searchTerm) || apellido.includes(searchTerm) || email.includes(searchTerm);
            
            // Para el filtro de proyecto, necesitamos obtener el nombre del proyecto seleccionado
            let matchesProyecto = true;
            if (selectedProyecto) {
                const selectedOption = filterProyecto.querySelector(`option[value="${selectedProyecto}"]`);
                const selectedProyectoNombre = selectedOption ? selectedOption.textContent : '';
                matchesProyecto = proyecto.includes(selectedProyectoNombre);
            }
            
            const matchesConsentimiento = !selectedConsentimiento || consentimiento.includes(selectedConsentimiento);

            if (matchesSearch && matchesProyecto && matchesConsentimiento) {
                row.style.display = 'table-row';
                visibleRows.push(row);
            } else {
                row.style.display = 'none';
            }
        });

        // Ordenamiento
        if (sortValue) {
            const tbody = table.querySelector('tbody');
            const sortedRows = Array.from(visibleRows).sort((a, b) => {
                const cellsA = a.querySelectorAll('td');
                const cellsB = b.querySelectorAll('td');
                
                switch(sortValue) {
                    case 'nombre_asc':
                        return cellsA[1].textContent.localeCompare(cellsB[1].textContent);
                    case 'nombre_desc':
                        return cellsB[1].textContent.localeCompare(cellsA[1].textContent);
                    case 'fecha_alta_asc':
                        return new Date(cellsA[7].textContent.split('/').reverse().join('-')) - new Date(cellsB[7].textContent.split('/').reverse().join('-'));
                    case 'fecha_alta_desc':
                        return new Date(cellsB[7].textContent.split('/').reverse().join('-')) - new Date(cellsA[7].textContent.split('/').reverse().join('-'));
                    default:
                        return 0;
                }
            });

            sortedRows.forEach(row => tbody.appendChild(row));
        }
    }

    searchInput.addEventListener('input', filterTable);
    filterProyecto.addEventListener('change', filterTable);
    filterConsentimiento.addEventListener('change', filterTable);
    sortBy.addEventListener('change', filterTable);
});

function sortTable(columnIndex) {
    // Implementar ordenamiento por columna
    console.log('Ordenar por columna:', columnIndex);
}

function verPaciente(id) {
    window.location.href = 'ver_paciente.php?id=' + id;
}

function editarPaciente(id) {
    window.location.href = 'editar_paciente.php?id=' + id;
}

function eliminarPaciente(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este paciente?')) {
        alert('Función de eliminar paciente en desarrollo para ID: ' + id);
    }
}

function validarEmail(input) {
    const email = input.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const errorDiv = document.getElementById('email-error');
    
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

