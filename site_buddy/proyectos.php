<?php
$pageTitle = 'Explorar Proyectos';
$rolColor = '#66BB6A'; // Verde de la imagen
include '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Obtener todos los proyectos con información del laboratorio patrocinante
$stmt = $db->query("
    SELECT p.*, l.nombre as laboratorio_nombre, l.pais as laboratorio_pais
    FROM proyectos p 
    JOIN laboratorios l ON p.patrocinante_id = l.id 
    WHERE p.activo = 1 
    ORDER BY p.fecha_alta DESC
");
$proyectos = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">
            <i class="fas fa-search me-2"></i>Explorar Proyectos Disponibles
        </h2>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text" style="background: #66BB6A; color: white;"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" id="searchInput" placeholder="Buscar proyectos...">
        </div>
    </div>
    <div class="col-md-6">
        <select class="form-select" id="filterLaboratorio">
            <option value="">Todos los patrocinantes</option>
            <?php
            $stmt = $db->query("SELECT DISTINCT l.id, l.nombre FROM laboratorios l JOIN proyectos p ON l.id = p.patrocinante_id WHERE l.activo = 1 ORDER BY l.nombre");
            $laboratorios = $stmt->fetchAll();
            foreach ($laboratorios as $lab): ?>
                <option value="<?php echo $lab['id']; ?>"><?php echo htmlspecialchars($lab['nombre']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="row" id="proyectosContainer">
    <?php foreach ($proyectos as $proyecto): ?>
    <div class="col-md-6 mb-4 proyecto-card" data-laboratorio="<?php echo $proyecto['patrocinante_id']; ?>">
        <div class="card h-100">
            <div class="card-header" style="background: #66BB6A; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-project-diagram me-2"></i><?php echo htmlspecialchars($proyecto['nombre']); ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong><i class="fas fa-flask me-1"></i>Patrocinante:</strong>
                    <span class="badge" style="background: #66BB6A; color: white;"><?php echo htmlspecialchars($proyecto['laboratorio_nombre']); ?></span>
                    <small class="text-muted">(<?php echo htmlspecialchars($proyecto['laboratorio_pais']); ?>)</small>
                </div>
                <div class="mb-3">
                    <strong><i class="fas fa-info-circle me-1"></i>Descripción:</strong>
                    <p class="card-text"><?php echo htmlspecialchars($proyecto['descripcion']); ?></p>
                </div>
                <div class="mb-3">
                    <strong><i class="fas fa-calendar me-1"></i>Fecha de Alta:</strong>
                    <span class="text-muted"><?php echo date('d/m/Y', strtotime($proyecto['fecha_alta'])); ?></span>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <button class="btn btn-outline-primary btn-sm" onclick="verDetalles(<?php echo $proyecto['id']; ?>)">
                        <i class="fas fa-eye me-1"></i>Ver Detalles
                    </button>
                    <button class="btn btn-sm" style="background: #66BB6A; color: white; border: none;" onclick="solicitarColaboracion(<?php echo $proyecto['id']; ?>)">
                        <i class="fas fa-handshake me-1"></i>Solicitar Colaboración
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row" id="noResults" style="display: none;">
    <div class="col-12">
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-2x mb-3"></i>
            <h4>No se encontraron proyectos</h4>
            <p>Intenta ajustar los filtros de búsqueda.</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterLaboratorio = document.getElementById('filterLaboratorio');
    const proyectoCards = document.querySelectorAll('.proyecto-card');
    const noResults = document.getElementById('noResults');

    function filterProjects() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedLaboratorio = filterLaboratorio.value;
        let visibleCount = 0;

        proyectoCards.forEach(card => {
            const projectName = card.querySelector('.card-title').textContent.toLowerCase();
            const projectDesc = card.querySelector('.card-text').textContent.toLowerCase();
            const laboratorioId = card.getAttribute('data-laboratorio');
            
            const matchesSearch = projectName.includes(searchTerm) || projectDesc.includes(searchTerm);
            const matchesLaboratorio = !selectedLaboratorio || laboratorioId === selectedLaboratorio;
            
            if (matchesSearch && matchesLaboratorio) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        noResults.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    searchInput.addEventListener('input', filterProjects);
    filterLaboratorio.addEventListener('change', filterProjects);
});

function verDetalles(id) {
    alert('Función de ver detalles en desarrollo para proyecto ID: ' + id);
}

function solicitarColaboracion(id) {
    if (confirm('¿Deseas solicitar colaboración en este proyecto?')) {
        alert('Función de solicitar colaboración en desarrollo para proyecto ID: ' + id);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
