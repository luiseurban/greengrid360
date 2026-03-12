<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - GreenGrid 360</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
    <div class="auth-container">
        <h1>GreenGrid 360</h1>
        <p class="subtitle">Crear cuenta</p>

        <?php if (!empty($error)): ?>
            <p class="auth-error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="index.php?accion=registro" class="auth-form">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="correo">Correo</label>
            <input type="email" id="correo" name="correo" required>

            <label for="password">Contrasena</label>
            <input type="password" id="password" name="password" required>

            <label for="confirmacion">Confirmar contrasena</label>
            <input type="password" id="confirmacion" name="confirmacion" required>

            <button type="submit">Registrarme</button>
        </form>

        <p class="auth-link">Ya tienes cuenta? <a href="index.php?accion=login">Inicia sesion</a></p>
    </div>
</body>
</html>
