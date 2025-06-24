<?php
session_start(); // Inicia la sesión

// 1. Verificación de Autenticación y Autorización
// Solo los administradores pueden acceder a esta página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: login.php"); // Redirige a login si no es admin o no está logueado
    exit();
}

// 2. Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "agencia");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$pedido_id = null;
$pedido = null;
$detalles_pedido = [];
$mensaje = ''; // Para mostrar mensajes de éxito o error

// 3. Procesar la solicitud POST para actualizar el estado del pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedido_id']) && isset($_POST['nuevo_estado'])) {
    $pedido_id_a_actualizar = filter_var($_POST['pedido_id'], FILTER_SANITIZE_NUMBER_INT);
    $nuevo_estado = $_POST['nuevo_estado'];

    // Lista de estados válidos (puedes personalizar esto)
    $estados_validos = ['Pendiente', 'Procesando', 'Enviado', 'Completado', 'Cancelado'];

    if (!in_array($nuevo_estado, $estados_validos)) {
        $mensaje = '<div class="alert alert-danger" role="alert">Estado no válido.</div>';
    } else {
        $stmt_update = $conexion->prepare("UPDATE pedidos SET Estado = ? WHERE ID_Pedido = ?");
        $stmt_update->bind_param("si", $nuevo_estado, $pedido_id_a_actualizar);

        if ($stmt_update->execute()) {
            $mensaje = '<div class="alert alert-success" role="alert">Estado del pedido actualizado correctamente a <strong>' . htmlspecialchars($nuevo_estado) . '</strong>.</div>';
            // Vuelve a cargar el pedido para mostrar el estado actualizado
            $pedido_id = $pedido_id_a_actualizar; // Asegura que se cargue el ID correcto
        } else {
            $mensaje = '<div class="alert alert-danger" role="alert">Error al actualizar el estado del pedido: ' . htmlspecialchars($stmt_update->error) . '</div>';
        }
        $stmt_update->close();
    }
}

// 4. Obtener el ID del pedido de la URL (GET) o del formulario (POST) para mostrar sus detalles
if (isset($_GET['id'])) {
    $pedido_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
} elseif (isset($_POST['pedido_id'])) { // Si se acaba de actualizar, se usa el ID del POST
    $pedido_id = filter_var($_POST['pedido_id'], FILTER_SANITIZE_NUMBER_INT);
}

if ($pedido_id) {
    // Consulta para obtener los detalles del pedido principal
    $stmt_pedido = $conexion->prepare("
        SELECT 
            p.ID_Pedido,
            p.Fecha,
            p.Estado,
            u.nombre AS nombre_usuario,
            u.apellido AS apellido_usuario,
            u.email AS email_usuario
        FROM 
            pedidos p
        JOIN 
            usuario u ON p.ID_Usuario = u.ID_Usuario
        WHERE 
            p.ID_Pedido = ?;
    ");
    $stmt_pedido->bind_param("i", $pedido_id);
    $stmt_pedido->execute();
    $resultado_pedido = $stmt_pedido->get_result();

    if ($resultado_pedido->num_rows === 1) {
        $pedido = $resultado_pedido->fetch_assoc();

        // Consulta para obtener los detalles de los items dentro de este pedido
        $stmt_detalles = $conexion->prepare("
            SELECT 
                pd.Cantidad,
                pa.nombre AS nombre_paquete,
                pa.precio AS precio_unitario
            FROM 
                pedido_detalles pd
            JOIN 
                paquetes pa ON pd.ID_Reserva = pa.ID_Reserva
            WHERE 
                pd.ID_Pedido = ?;
        ");
        $stmt_detalles->bind_param("i", $pedido_id);
        $stmt_detalles->execute();
        $resultado_detalles = $stmt_detalles->get_result();
        while ($detalle = $resultado_detalles->fetch_assoc()) {
            $detalles_pedido[] = $detalle;
        }
        $stmt_detalles->close();

    } else {
        $mensaje = '<div class="alert alert-warning" role="alert">Pedido no encontrado.</div>';
        $pedido_id = null; // Reinicia el ID si el pedido no se encontró
    }
    $stmt_pedido->close();
} else {
    $mensaje = '<div class="alert alert-warning" role="alert">No se ha especificado un ID de pedido.</div>';
}

$conexion->close(); // Cierra la conexión a la base de datos
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Pedido y Cambio de Estado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f3f6f9; }
        .container { margin-top: 20px; }
        .card-header { font-weight: bold; }
        .card-body p { margin-bottom: 5px; }
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
                        <li class="nav-item"><a class="nav-link" href="agregar_paquete.php">Agregar Producto</a></li>
                        <li class="nav-item"><a class="nav-link" href="gestion_pedidos.php">Gestión Pedidos</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="mb-4">Detalles del Pedido y Cambio de Estado</h1>
        <a href="gestion_pedidos.php" class="btn btn-secondary mb-3">← Volver a Gestión de Pedidos</a>

        <?php echo $mensaje; // Muestra el mensaje de éxito/error ?>

        <?php if ($pedido): ?>
            <div class="card mb-4">
                <div class="card-header">
                    Pedido #<?php echo htmlspecialchars($pedido['ID_Pedido']); ?>
                </div>
                <div class="card-body">
                    <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['nombre_usuario'] . ' ' . $pedido['apellido_usuario']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($pedido['email_usuario']); ?></p>
                    <p><strong>Fecha:</strong> <?php echo htmlspecialchars($pedido['Fecha']); ?></p>
                    <p><strong>Estado Actual:</strong> <span class="badge <?php 
                        if ($pedido['Estado'] == 'Pendiente') echo 'bg-warning';
                        else if ($pedido['Estado'] == 'Completado') echo 'bg-success';
                        else if ($pedido['Estado'] == 'Cancelado') echo 'bg-danger';
                        else echo 'bg-info'; // Para otros estados como Procesando, Enviado
                    ?>"><?php echo htmlspecialchars($pedido['Estado']); ?></span></p>

                    <h5 class="mt-4">Paquetes en el Pedido:</h5>
                    <ul class="list-group mb-3">
                        <?php 
                        $total_pedido = 0;
                        foreach ($detalles_pedido as $detalle): 
                            $subtotal_item = $detalle['Cantidad'] * $detalle['precio_unitario'];
                            $total_pedido += $subtotal_item;
                        ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($detalle['nombre_paquete']); ?> (x<?php echo htmlspecialchars($detalle['Cantidad']); ?>)
                                <span class="badge bg-primary rounded-pill">$<?php echo number_format($subtotal_item, 2); ?></span>
                            </li>
                        <?php endforeach; ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-light">
                            <strong>Total del Pedido:</strong>
                            <strong>$<?php echo number_format($total_pedido, 2); ?></strong>
                        </li>
                    </ul>

                    <h5 class="mt-4">Cambiar Estado:</h5>
                    <form method="POST" action="cambiar_estado_pedido.php">
                        <input type="hidden" name="pedido_id" value="<?php echo htmlspecialchars($pedido['ID_Pedido']); ?>">
                        <div class="mb-3">
                            <label for="nuevo_estado" class="form-label">Seleccionar Nuevo Estado:</label>
                            <select class="form-select" id="nuevo_estado" name="nuevo_estado" required>
                                <option value="Pendiente" <?php if ($pedido['Estado'] == 'Pendiente') echo 'selected'; ?>>Pendiente</option>
                                <option value="Procesando" <?php if ($pedido['Estado'] == 'Procesando') echo 'selected'; ?>>Procesando</option>
                                <option value="Enviado" <?php if ($pedido['Estado'] == 'Enviado') echo 'selected'; ?>>Enviado</option>
                                <option value="Completado" <?php if ($pedido['Estado'] == 'Completado') echo 'selected'; ?>>Completado</option>
                                <option value="Cancelado" <?php if ($pedido['Estado'] == 'Cancelado') echo 'selected'; ?>>Cancelado</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Actualizar Estado</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <footer class="text-center mt-5 p-4 bg-light">
        <small>&copy; 2025 Agencia de Viajes. Todos los derechos reservados.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>