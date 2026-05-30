<?php
$has_session = isset($_SESSION['usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de usuario | GreenGrid 360</title>
    <link rel="stylesheet" href="../css/index.css">
    <style>
        .manual-container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 6px; }
        .manual-embed { width: 100%; height: 720px; border: 1px solid #e6e6e6; }
        .manual-actions { text-align: right; margin-top: 12px; }
    </style>
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
        <section class="section-block">
            <div class="section-heading">
                <p class="eyebrow">Manual de usuario</p>
                <h2>Guía de uso - GreenGrid 360</h2>
                <p>Este documento contiene instrucciones para operar y administrar la plataforma GreenGrid 360.</p>
            </div>

            <div class="manual-container">
                <iframe class="manual-embed" src="docs/Manual de usuario.pdf"></iframe>

                <div class="manual-actions">
                    <a class="btn btn-primary" href="docs/Manual de usuario.pdf" download>Descargar</a>
                </div>
            </div>
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
