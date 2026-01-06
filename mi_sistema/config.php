<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'taller_mecanico');

// Configuración de la aplicación
define('BASE_URL', 'http://localhost/ejemplo1/');
define('SITE_NAME', 'Sistema de Taller Mecánico');

// Zona horaria
date_default_timezone_set('America/La_Paz');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clase de conexión a la base de datos
class Database
{
    private static $instance = null;
    private $conn;

    private function __construct()
    {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }
}

// Función para verificar autenticación
function isAuthenticated()
{
    return isset($_SESSION['usuario_id']) && isset($_SESSION['rol']);
}

// Función para verificar rol
function hasRole($role)
{
    return isAuthenticated() && $_SESSION['rol'] === $role;
}

// Función para redirigir según rol
function redirectToRole()
{
    if (!isAuthenticated()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }

    switch ($_SESSION['rol']) {
        case 'encargado':
            header('Location: ' . BASE_URL . 'encargado/dashboard.php');
            break;
        case 'trabajador':
            header('Location: ' . BASE_URL . 'trabajador/dashboard.php');
            break;
        case 'cliente':
            header('Location: ' . BASE_URL . 'cliente/dashboard.php');
            break;
        default:
            header('Location: ' . BASE_URL . 'logout.php');
    }
    exit;
}

// Función para proteger páginas
function requireAuth($allowedRoles = [])
{
    if (!isAuthenticated()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }

    if (!empty($allowedRoles) && !in_array($_SESSION['rol'], $allowedRoles)) {
        header('Location: ' . BASE_URL . 'unauthorized.php');
        exit;
    }
}

// Función para sanitizar datos
function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

// Función para formatear fecha
function formatDate($date)
{
    return date('d/m/Y H:i', strtotime($date));
}

// Función para formatear moneda
function formatCurrency($amount)
{
    return 'Bs. ' . number_format($amount, 2);
}