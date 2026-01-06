<?php
require_once 'config.php';

// Si ya está autenticado, redirigir al dashboard correspondiente
if (isAuthenticated()) {
    redirectToRole();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body class="landing-page">
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <img src="assets/img/sector1.png" alt="error de carga" width="150">
            </div>
            <div class="nav-menu">
                <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
                <a href="register.php" class="btn btn-primary">Registrarse</a>
            </div>
        </div>
    </nav>

    <header class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Servicio Profesional para tu Vehículo</h1>
                <p>Expertos en mantenimiento y reparación automotriz. Agenda tu cita hoy mismo.</p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn btn-outline btn-large">Solicitar Servicio</a>
                    <a href="#servicios" class="btn btn-outline btn-large">Ver Servicios</a>
                </div>
            </div>
        </div>
    </header>

    <section id="servicios" class="services">
        <div class="container">
            <h2>Nuestros Servicios</h2>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">🛢️</div>
                    <h3>Cambio de Aceite</h3>
                    <p>Mantenimiento preventivo para el motor de tu vehículo</p>
                    <span class="price">Desde Bs. 250</span>
                </div>
                <div class="service-card">
                    <div class="service-icon">⚙️</div>
                    <h3>Afinación</h3>
                    <p>Afinación completa para el óptimo rendimiento</p>
                    <span class="price">Desde Bs. 1,500</span>
                </div>
                <div class="service-card">
                    <div class="service-icon">🛑</div>
                    <h3>Sistema de Frenos</h3>
                    <p>Reparación y mantenimiento de frenos</p>
                    <span class="price">Desde Bs. 2,000</span>
                </div>
                <div class="service-card">
                    <div class="service-icon">🔧</div>
                    <h3>Suspensión</h3>
                    <p>Reparación completa del sistema de suspensión</p>
                    <span class="price">Desde Bs. 2,500</span>
                </div>
                <div class="service-card">
                    <div class="service-icon">❄️</div>
                    <h3>Aire Acondicionado</h3>
                    <p>Recarga y reparación de sistemas de A/C</p>
                    <span class="price">Desde Bs. 800</span>
                </div>
                <div class="service-card">
                    <div class="service-icon">🔍</div>
                    <h3>Diagnóstico</h3>
                    <p>Revisión completa y diagnóstico electrónico</p>
                    <span class="price">Desde Bs. 500</span>
                </div>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <h2>¿Por qué elegirnos?</h2>
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">👨‍🔧</div>
                    <h3>Mecánicos Certificados</h3>
                    <p>Personal altamente capacitado y con experiencia</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">⏱️</div>
                    <h3>Servicio Rápido</h3>
                    <p>Respetamos los tiempos de entrega acordados</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">💰</div>
                    <h3>Precios Justos</h3>
                    <p>Cotizaciones transparentes y competitivas</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">✅</div>
                    <h3>Garantía</h3>
                    <p>Todos nuestros trabajos están garantizados</p>
                </div>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="container">
            <h2>¿Listo para comenzar?</h2>
            <p>Crea tu cuenta y solicita tu servicio en minutos</p>
            <a href="register.php" class="btn btn-primary btn-large">Crear Cuenta Ahora</a>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Taller Mecánico Pro. Todos los derechos reservados.</p>
            <p>Contacto: info@tallerpro.com | Tel: 555-0000</p>
        </div>
    </footer>
</body>

</html>