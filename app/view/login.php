<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesion - GreenGrid 360</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body class="page-auth">
    <div class="auth-shell">
        <div class="auth-container">
            <div class="auth-header">
                <p class="brand-tag">GreenGrid 360</p>
                <h1>Bienvenido</h1>
                <p class="subtitle">Inicia sesion para consultar las mediciones</p>
            </div>

            <?php if (!empty($error)): ?>
                <p class="auth-error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <?php if (!empty($mensaje)): ?>
                <p class="auth-success"><?php echo htmlspecialchars($mensaje); ?></p>
            <?php endif; ?>

            <form method="POST" action="index.php?accion=login" class="auth-form">
                <label for="correo">Correo</label>
                <input type="email" id="correo" name="correo" required>

                <label for="password">Contrasena</label>
                <input type="password" id="password" name="password" required>

                <button type="submit" class="auth-btn">Entrar</button>
            </form>

            <p class="auth-link">No tienes cuenta? <a href="index.php?accion=registro">Registrate</a></p>
            <p class="auth-note">Demo local: admin@localhost / admin123</p>
        </div>
    </div>
</body>
</html>
