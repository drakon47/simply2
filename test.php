<?php
header('Content-Type: text/html; charset=UTF-8');
echo "PHP está funcionando correctamente!<br>";
echo "Versión de PHP: " . phpversion() . "<br>";
echo "Directorio actual: " . __DIR__ . "<br>";

// Probar conexión a la base de datos
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    echo "Conexión a la base de datos: ✅ EXITOSA<br>";
    
    // Probar consulta
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch();
    echo "Usuarios en la base de datos: " . $result['total'] . "<br>";
    
    // Probar caracteres especiales
    $stmt = $db->query("SELECT nombre, apellido FROM usuarios WHERE email = 'centro@test.com'");
    $user = $stmt->fetch();
    echo "Usuario de prueba: " . $user['nombre'] . " " . $user['apellido'] . "<br>";
    
} catch (Exception $e) {
    echo "Error de base de datos: " . $e->getMessage() . "<br>";
}
?>
