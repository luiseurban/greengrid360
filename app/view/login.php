<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesion - GreenGrid 360</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
    <div class="auth-container">
        <h1>GreenGrid 360</h1>
        <p class="subtitle">Iniciar sesion</p>

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

            <button type="submit">Entrar</button>
        </form>

        <p class="auth-link">No tienes cuenta? <a href="index.php?accion=registro">Registrate</a></p>
        <p class="auth-note">Demo local: admin@localhost / admin123</p>
    </div>
</body>
</html>
