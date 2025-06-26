<?php
session_start();

// Redirigir si no hay un usuario logueado o si es un admin
if (!isset($_SESSION['usuario_id']) || (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin')) {
    header("Location: login.php");
    exit();
}

$status = isset($_GET['status']) ? $_GET['status'] : 'unknown'; // success, error, unknown

$mensaje_titulo = "";
$mensaje_texto = "";
$clase_alerta = "";
$icono = "";

if ($status === 'success') {
    $mensaje_titulo = "¡Compra Exitosa!";
    $mensaje_texto = "Tu pago ha sido procesado correctamente y tu pedido ha sido registrado. ¡Gracias por tu compra!";
    $clase_alerta = "alert-success";
    $icono = "✅"; // Unicode check mark
} elseif ($status === 'error') {
    $mensaje_titulo = "Error en el Pago";
    $mensaje_texto = "Hubo un problema al procesar tu pago. Por favor, inténtalo de nuevo o contacta a soporte.";
    $clase_alerta = "alert-danger";
    $icono = "❌"; // Unicode cross mark
} else {
    $mensaje_titulo = "Estado de Compra Desconocido";
    $mensaje_texto = "No pudimos determinar el estado de tu compra. Si tienes dudas, revisa 'Mis Pedidos'.";
    $clase_alerta = "alert-warning";
    $icono = "❓"; // Unicode question mark
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f3f6f9; }
        .confirmation-card {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            background: #fff;
            text-align: center;
        }
        .confirmation-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Agencia de Viajes</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="gestion_pedidos.php">Gestión Pedidos</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Cerrar Sesión (Admin)</a></li>
                    <?php elseif (isset($_SESSION['usuario_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="carrito.php">Carrito</a></li>
                        <li class="nav-item"><a class="nav-link" href="pedidos.php">Mis Pedidos</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Iniciar Sesión</a></li>
                        <li class="nav-item"><a class="nav-link" href="registro.php">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card confirmation-card <?php echo $clase_alerta; ?>">
            <div class="card-body">
                <div class="confirmation-icon"><?php echo $icono; ?></div>
                <h2 class="card-title"><?php echo $mensaje_titulo; ?></h2>
                <p class="card-text"><?php echo $mensaje_texto; ?></p>
                
                <?php if ($status === 'success'): ?>
                    <p class="mt-4">Puedes ver los detalles de tu pedido en <a href="pedidos.php" class="alert-link">Mis Pedidos</a>.</p>
                <?php endif; ?>

                <div class="d-grid gap-2 mt-4">
                    <a href="index.php" class="btn btn-primary btn-lg">Volver a la Tienda</a>
                    <?php if ($status === 'error'): ?>
                        <a href="carrito.php" class="btn btn-outline-secondary">Volver al Carrito</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center mt-5 p-4 bg-light">
        <small>&copy; 2025 Agencia de Viajes. Todos los derechos reservados.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>