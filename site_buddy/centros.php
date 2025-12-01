<?php
$pageTitle = 'Centros Disponibles';
$rolColor = '#66BB6A'; // Verde de la imagen
include '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Obtener todos los centros
$stmt = $db->query("SELECT * FROM centros WHERE activo = 1 ORDER BY nombre");
$centros = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">
            <i class="fas fa-hospital me-2"></i>Centros Médicos Disponibles
        </h2>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="input-group">
            <span class="input-group-text" style="background: #66BB6A; color: white;"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" id="searchInput" placeholder="Buscar centros...">
        </div>
    </div>
    <div class="col-md-4">
        <select class="form-select" id="filterProvincia">
            <option value="">Todas las provincias</option>
            <?php
            $stmt = $db->query("SELECT DISTINCT provincia FROM centros WHERE activo = 1 ORDER BY provincia");
            $provincias = $stmt->fetchAll();
            foreach ($provincias as $provincia): ?>
                <option value="<?php echo htmlspecialchars($provincia['provincia']); ?>"><?php echo htmlspecialchars($provincia['provincia']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <select class="form-select" id="filterPais">
            <option value="">Todos los países</option>
            <?php
            $stmt = $db->query("SELECT DISTINCT pais FROM centros WHERE activo = 1 ORDER BY pais");
            $paises = $stmt->fetchAll();
            foreach ($paises as $pais): ?>
                <option value="<?php echo htmlspecialchars($pais['pais']); ?>"><?php echo htmlspecialchars($pais['pais']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="row" id="centrosContainer">
    <?php foreach ($centros as $centro): ?>
    <div class="col-md-6 mb-4 centro-card" 
         data-provincia="<?php echo htmlspecialchars($centro['provincia']); ?>" 
         data-pais="<?php echo htmlspecialchars($centro['pais']); ?>">
        <div class="card h-100">
            <div class="card-header" style="background: #66BB6A; color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-hospital me-2"></i><?php echo htmlspecialchars($centro['nombre']); ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong><i class="fas fa-map-marker-alt me-1"></i>Ubicación:</strong>
                    <p class="mb-1"><?php echo htmlspecialchars($centro['direccion']); ?></p>
                    <p class="mb-1"><?php echo htmlspecialchars($centro['localidad'] . ', ' . $centro['provincia']); ?></p>
                    <span class="badge" style="background: #66BB6A; color: white;"><?php echo htmlspecialchars($centro['pais']); ?></span>
                </div>
                
                <?php if ($centro['email_referencia'] || $centro['email_referencia_2']): ?>
                <div class="mb-3">
                    <strong><i class="fas fa-envelope me-1"></i>Email<?php echo ($centro['email_referencia'] && $centro['email_referencia_2']) ? 's' : ''; ?>:</strong>
                    <?php if ($centro['email_referencia']): ?>
                    <div>
                        <a href="mailto:<?php echo htmlspecialchars($centro['email_referencia']); ?>" class="text-decoration-none">
                            <?php echo htmlspecialchars($centro['email_referencia']); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if ($centro['email_referencia_2']): ?>
                    <div>
                        <a href="mailto:<?php echo htmlspecialchars($centro['email_referencia_2']); ?>" class="text-decoration-none">
                            <?php echo htmlspecialchars($centro['email_referencia_2']); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($centro['telefono']): ?>
                <div class="mb-3">
                    <strong><i class="fas fa-phone me-1"></i>Teléfono:</strong>
                    <a href="tel:<?php echo htmlspecialchars($centro['telefono']); ?>" class="text-decoration-none">
                        <?php echo htmlspecialchars($centro['telefono']); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <button class="btn btn-outline-info btn-sm" onclick="verDetalles(<?php echo $centro['id']; ?>)">
                        <i class="fas fa-info-circle me-1"></i>Ver Detalles
                    </button>
                    <button class="btn btn-sm" style="background: #66BB6A; color: white; border: none;" onclick="contactarCentro(<?php echo $centro['id']; ?>)">
                        <i class="fas fa-envelope me-1"></i>Contactar
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
            <h4>No se encontraron centros</h4>
            <p>Intenta ajustar los filtros de búsqueda.</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterProvincia = document.getElementById('filterProvincia');
    const filterPais = document.getElementById('filterPais');
    const centroCards = document.querySelectorAll('.centro-card');
    const noResults = document.getElementById('noResults');

    function filterCentros() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedProvincia = filterProvincia.value;
        const selectedPais = filterPais.value;
        let visibleCount = 0;

        centroCards.forEach(card => {
            const centroName = card.querySelector('.card-title').textContent.toLowerCase();
            const centroLocation = card.querySelector('p').textContent.toLowerCase();
            const provincia = card.getAttribute('data-provincia');
            const pais = card.getAttribute('data-pais');
            
            const matchesSearch = centroName.includes(searchTerm) || centroLocation.includes(searchTerm);
            const matchesProvincia = !selectedProvincia || provincia === selectedProvincia;
            const matchesPais = !selectedPais || pais === selectedPais;
            
            if (matchesSearch && matchesProvincia && matchesPais) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        noResults.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    searchInput.addEventListener('input', filterCentros);
    filterProvincia.addEventListener('change', filterCentros);
    filterPais.addEventListener('change', filterCentros);
});

function verDetalles(id) {
    alert('Función de ver detalles en desarrollo para centro ID: ' + id);
}

function contactarCentro(id) {
    if (confirm('¿Deseas contactar con este centro médico?')) {
        alert('Función de contacto en desarrollo para centro ID: ' + id);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
