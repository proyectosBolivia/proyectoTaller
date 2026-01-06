<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            padding: 2rem;
        }

        .error-container {
            background: white;
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            max-width: 600px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .error-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }

        .error-container h1 {
            color: #ef4444;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .error-container p {
            color: #64748b;
            font-size: 1.125rem;
            margin-bottom: 2rem;
        }

        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
    </style>
</head>

<body class="error-page">
    <div class="error-container">
        <div class="error-icon">🚫</div>
        <h1>Acceso Denegado</h1>
        <p>No tienes permisos para acceder a esta página. Por favor, verifica tus credenciales o contacta al
            administrador del sistema.</p>

        <div class="error-actions">
            <?php if (isAuthenticated()): ?>
                <a href="javascript:history.back()" class="btn btn-secondary">Volver Atrás</a>
                <?php
                switch ($_SESSION['rol']) {
                    case 'encargado':
                        echo '<a href="encargado/dashboard.php" class="btn btn-primary">Ir a Mi Dashboard</a>';
                        break;
                    case 'trabajador':
                        echo '<a href="trabajador/dashboard.php" class="btn btn-primary">Ir a Mi Dashboard</a>';
                        break;
                    case 'cliente':
                        echo '<a href="cliente/dashboard.php" class="btn btn-primary">Ir a Mi Dashboard</a>';
                        break;
                }
                ?>
                <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
            <?php else: ?>
                <a href="index.php" class="btn btn-secondary">Ir al Inicio</a>
                <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>