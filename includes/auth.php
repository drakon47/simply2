<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function login($email, $password) {
        $stmt = $this->db->prepare("
            SELECT u.*, 
                   CASE 
                       WHEN u.rol = 'CENTRO' THEN c.nombre
                       WHEN u.rol = 'LABORATORIO' THEN l.nombre
                       ELSE NULL
                   END as entidad_nombre
            FROM usuarios u
            LEFT JOIN centros c ON u.rol = 'CENTRO' AND u.idEntidad = c.id
            LEFT JOIN laboratorios l ON u.rol = 'LABORATORIO' AND u.idEntidad = l.id
            WHERE u.email = ? AND u.activo = 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && md5($password) === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_apellido'] = $user['apellido'];
            $_SESSION['user_rol'] = $user['rol'];
            $_SESSION['user_identidad'] = $user['idEntidad'];
            $_SESSION['user_entidad_nombre'] = $user['entidad_nombre'];
            return true;
        }
        return false;
    }
    
    public function logout() {
        session_destroy();
        header('Location: ../index.php');
        exit();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ../index.php');
            exit();
        }
    }
    
    public function getUserInfo() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'],
                'nombre' => $_SESSION['user_nombre'],
                'apellido' => $_SESSION['user_apellido'],
                'rol' => $_SESSION['user_rol'],
                'identidad' => $_SESSION['user_identidad'] ?? null,
                'entidad_nombre' => $_SESSION['user_entidad_nombre'] ?? null
            ];
        }
        return null;
    }
    
    public function redirectByRole() {
        if ($this->isLoggedIn()) {
            $rol = $_SESSION['user_rol'];
            switch($rol) {
                case 'CENTRO':
                    header('Location: site_centro/index.php');
                    break;
                case 'LABORATORIO':
                    header('Location: site_laboratorio/index.php');
                    break;
                case 'BUDDY':
                    header('Location: site_buddy/index.php');
                    break;
                case 'ADMIN':
                    header('Location: site_admin/index.php');
                    break;
            }
            exit();
        }
    }
}
?>
