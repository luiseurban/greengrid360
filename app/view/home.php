<?php
$has_session = isset($_SESSION['usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenGrid 360 | Tecnología sostenible para Yumbo</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body class="landing-page">
    <header class="landing-header">
        <div class="landing-brand">
            <span class="brand-mark">GreenGrid 360</span>
        </div>

        <nav class="landing-nav" aria-label="Navegacion principal">
            <a href="index.php?accion=listar">Datos</a>
            <a href="index.php?accion=registro">Registro</a>
            <a class="btn btn-secondary btn-login" href="index.php?accion=<?php echo $has_session ? 'listar' : 'login'; ?>">
                <?php echo $has_session ? 'Dashboard' : 'Iniciar sesion'; ?>
            </a>
        </nav>
    </header>

    <main class="landing-shell">
        <section class="hero-panel">
            <div class="hero-copy">
                <p class="eyebrow">Monitoreo ambiental inteligente</p>
                <h1>GreenGrid 360 para el cuidado de espacios verdes en Yumbo</h1>
                <p class="hero-text">
                    GreenGrid 360 es una iniciativa de tecnología sostenible enfocada en el seguimiento de espacios verdes mediante
                    sensores ambientales, conectividad ESP32 y una plataforma digital pensada para observar el estado ambiental en tiempo real.
                </p>
                <p class="hero-text">
                    El sistema permite recopilar y visualizar variables como temperatura, humedad, calidad del aire y lluvia para apoyar
                    decisiones de conservación, cuidado preventivo y gestión responsable del entorno.
                </p>

                <div class="hero-actions">
                    <a class="btn btn-primary" href="index.php?accion=listar">Dashboard</a>
                    <a class="btn btn-secondary" href="index.php?accion=manual">Manual de usuario</a>
                </div>

                <div class="hero-notes">
                    <article>
                        <strong>Enfoque</strong>
                        <span>Sostenibilidad, monitoreo continuo y visualizacion clara.</span>
                    </article>
                    <article>
                        <strong>Alcance</strong>
                        <span>Espacios verdes del municipio de Yumbo con datos operativos útiles.</span>
                    </article>
                </div>
            </div>

            <div class="hero-visual">
                <div class="image-frame image-frame-large">
                    <img src="img/principal.jpg" alt="GreenGrid 360">
                </div>
            </div>
        </section>

        <section class="section-block">
            <div class="section-heading">
                <p class="eyebrow">Como se diseña</p>
                <h2>Arquitectura del proyecto</h2>
                <p>
                    La solución se construye en capas para separar captura de datos, almacenamiento, consulta y presentación.
                    Eso facilita el mantenimiento y hace posible ampliar el sistema en el futuro.
                </p>
            </div>

            <div class="design-grid">
                <article class="design-card">
                    <h3>1. Captura de datos</h3>
                    <p>
                        Los sensores instalados en campo registran temperatura, humedad, lluvia y calidad del aire. El ESP32 centraliza
                        la lectura y prepara la información para su envío.
                    </p>
                </article>

                <article class="design-card">
                    <h3>2. Transmision y almacenaje</h3>
                    <p>
                        Los valores se integran con la capa de persistencia para guardarse en base de datos. Esto permite conservar
                        historiales, consultar registros y alimentar reportes posteriores.
                    </p>
                </article>

                <article class="design-card">
                    <h3>3. Visualizacion</h3>
                    <p>
                        Una interfaz web muestra los registros capturados, filtros de consulta y el acceso a la información ya
                        exportada a base de datos.
                    </p>
                </article>
            </div>
        </section>

        <section class="section-block split-layout">
            <div>
                <p class="eyebrow">Propuesta de valor</p>
                <h2>Informacion extendida del proyecto</h2>
                <p>
                    GreenGrid 360 promueve el compromiso ambiental, el trabajo en equipo, la innovación sostenible y la responsabilidad social.
                    Su propósito es apoyar la vigilancia de zonas verdes para anticipar cambios en el ambiente, reducir riesgos y mejorar
                    la toma de decisiones sobre mantenimiento o intervención.
                </p>
                <p>
                    El proyecto se alinea con objetivos de desarrollo sostenible relacionados con bienestar, entornos saludables y uso
                    responsable de la tecnología en beneficio de la comunidad.
                </p>
                <ul class="feature-list">
                    <li>Monitoreo continuo de variables ambientales</li>
                    <li>Consulta de datos exportados a base de datos</li>
                    <li>Base preparada para crecer con nuevas mediciones</li>
                    <li>Interfaz simple para usuarios y operadores</li>
                </ul>
            </div>

            <div class="blank-gallery">
                <div class="image-frame image-frame-medium">
                    <img src="img/secundaria.jpg" alt="Arquitectura GreenGrid 360">
                </div>
                <div class="image-frame image-frame-small">
                    <img src="img/soporte.png" alt="Soporte visual GreenGrid 360">
                </div>
            </div>
        </section>

        <section class="section-block cta-band">
            <div>
                <p class="eyebrow">Acceso rapido</p>
                <h2>Ir a la informacion almacenada</h2>
                <p>
                    Este boton lleva al panel que consulta la informacion exportada a base de datos. Si no has iniciado sesion,
                    el sistema te pedira autenticarte primero.
                </p>
            </div>
            <a class="btn btn-primary btn-large" href="index.php?accion=listar">Abrir Dashboard</a>
        </section>
    </main>

    <footer class="landing-footer">
        <p>GreenGrid 360 - Monitoreo ambiental y tecnologia sostenible para espacios verdes.</p>
        <div class="footer-right">
            <a href="index.php?accion=<?php echo $has_session ? 'listar' : 'login'; ?>">
                <?php echo $has_session ? 'Dashboard' : 'Acceso al sistema'; ?>
            </a>
            <span class="copyright">&copy; 2026 GreenGrid 360. Todos los derechos reservados.</span>
        </div>
    </footer>
</body>
</html>