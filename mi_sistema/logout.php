<?php
require_once 'config.php';

// Destruir sesión
session_destroy();

// Redirigir al login
header('Location: ' . BASE_URL . 'login.php');
exit;
?>