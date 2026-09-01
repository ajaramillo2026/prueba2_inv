<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema - Bootstrap 5.3.8</title>
    <!-- CSS de Bootstrap v5.3.8 Oficial -->
    <!-- Reemplaza la línea del CSS en el <head> por esta: -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">


</head>
<body class="bg-dark d-flex align-items-center justify-content-center vh-100">

<div class="card shadow-lg border-0" style="width: 25rem; border-radius: 12px;">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <h4 class="fw-bold text-primary mb-1">🎫 Sistema Soporte</h4>
            <span class="text-muted small">Introduce tus credenciales de acceso</span>
        </div>

        <?php if (isset($_SESSION['error_login'])): ?>
            <div class="alert alert-danger p-2 small text-center" role="alert">
                <?php 
                    echo htmlspecialchars($_SESSION['error_login']); 
                    unset($_SESSION['error_login']);
                ?>
            </div>
        <?php endif; ?>

        <form action="index.php?accion=procesar_login" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Usuario de Acceso</label>
                <input type="text" name="usuario" class="form-control" placeholder="Ej: admin01" required autocomplete="off">
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold">Contraseña</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary fw-bold py-2">Iniciar Sesión</button>
            </div>
        </form>
    </div>
</div>


    <!-- Reemplaza la línea del JS al final antes del </body> por esta: -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
