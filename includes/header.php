<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getUserInfo();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Proyecto Simply'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .navbar-brand {
            font-weight: 600;
            color: <?php echo $rolColor ?? '#667eea'; ?> !important;
        }
        .navbar {
            background: linear-gradient(135deg, <?php echo $rolColor ?? '#667eea'; ?> 0%, <?php echo $rolColor ?? '#764ba2'; ?> 100%) !important;
        }
        .navbar-brand, .navbar-nav .nav-link {
            color: white !important;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-avatar {
            width: 35px;
            height: 35px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .btn-logout {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid white;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-logout:hover {
            background: white;
            color: <?php echo $rolColor ?? '#667eea'; ?>;
            transform: translateY(-1px);
        }
        .role-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
            border: 1px solid white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-flask me-2"></i>Proyecto Simply
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['nombre'], 0, 1) . substr($user['apellido'], 0, 1)); ?>
                    </div>
                    <div>
                        <div class="fw-bold"><?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?></div>
                        <div class="role-badge"><?php echo $user['rol']; ?></div>
                        <?php if ($user['entidad_nombre']): ?>
                        <div class="entidad-info" style="font-size: 0.75rem; color: rgba(255,255,255,0.8); margin-top: 2px;">
                            <i class="fas fa-building me-1"></i><?php echo htmlspecialchars($user['entidad_nombre']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <a href="../logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
