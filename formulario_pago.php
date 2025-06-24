<?php
session_start();

// Redirigir si no hay un usuario logueado o si es un admin
if (!isset($_SESSION['usuario_id']) || (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin')) {
    header("Location: login.php");
    exit();
}

// Obtener el total del carrito de la sesión
$total_a_pagar = isset($_SESSION['total_carrito']) ? $_SESSION['total_carrito'] : '0.00'; 

// Si el carrito está vacío o el total es 0, redirigir al carrito
if ($total_a_pagar <= 0 || !isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar con Tarjeta - Agencia de Viajes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f3f6f9; }
        .payment-form-card {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            background: #fff;
        }
        .form-label { font-weight: bold; }
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
        <div class="card payment-form-card">
            <h2 class="card-title text-center mb-4">Realizar Pago</h2>
            <p class="text-center lead">Total a pagar: <strong>$<?php echo number_format($total_a_pagar, 2); ?></strong></p>
            <form action="procesar_pago.php" method="POST"> <div class="mb-3">
                    <label for="nombre_tarjeta" class="form-label">Nombre en la Tarjeta</label>
                    <input type="text" class="form-control" id="nombre_tarjeta" name="nombre_tarjeta" required placeholder="Ej: Juan Pérez">
                </div>
                <div class="mb-3">
                    <label for="numero_tarjeta" class="form-label">Número de Tarjeta</label>
                    <input type="text" class="form-control" id="numero_tarjeta" name="numero_tarjeta" required pattern="\d{13,16}" title="Ingrese un número de tarjeta válido (13 a 16 dígitos)" placeholder="XXXX XXXX XXXX XXXX">
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="fecha_expiracion" class="form-label">Fecha de Expiración</label>
                        <input type="text" class="form-control" id="fecha_expiracion" name="fecha_expiracion" required pattern="(0[1-9]|1[0-2])\/\d{2}" title="Formato MM/AA" placeholder="MM/AA">
                    </div>
                    <div class="col-md-6">
                        <label for="cvv" class="form-label">CVV</label>
                        <input type="text" class="form-control" id="cvv" name="cvv" required pattern="\d{3,4}" title="3 o 4 dígitos" placeholder="XXX">
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">Pagar Ahora</button>
                    <a href="carrito.php" class="btn btn-outline-secondary">Volver al Carrito</a>
                </div>
            </form>
        </div>
    </div>

    <footer class="text-center mt-5 p-4 bg-light">
        <small>&copy; 2025 Agencia de Viajes. Todos los derechos reservados.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>