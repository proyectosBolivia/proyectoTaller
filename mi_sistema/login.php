<?php
require_once 'config.php';

if (isAuthenticated()) {
    redirectToRole();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Por favor, complete todos los campos';
    } else {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT u.*, r.nombre as rol 
            FROM usuarios u 
            INNER JOIN roles r ON u.rol_id = r.id 
            WHERE u.email = ? AND u.activo = 1
        ");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['rol'] = $usuario['rol'];

            redirectToRole();
        } else {
            $error = 'Credenciales incorrectas';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <img src="assets/img/sector1.png" alt="error de carga" width="150">
                <h2>Iniciar Sesión</h2>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
            </form>

            <div class="auth-footer">
                <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
                <p><a href="index.php">Volver al inicio</a></p>
            </div>

            <div class="auth-demo">
                <p><strong>Usuarios de prueba:</strong></p>
                <ul>
                    <li><strong>Encargado:</strong> encargado@taller.com</li>
                    <li><strong>Trabajador:</strong> juan.perez@taller.com</li>
                    <li><strong>Cliente:</strong> ana.martinez@email.com</li>
                    <li><strong>Contraseña:</strong> 123456</li>
                </ul>
            </div>
        </div>
    </div>
</body>

</html>