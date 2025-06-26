<?php
session_start(); // Inicia la sesión para acceder a las variables de sesión

// 1. Verificación de Autenticación y Autorización
// Si el usuario no está logueado o no es un administrador, redirige.
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: login.php"); // Redirige a la página de login si no es admin o no está logueado
    exit();
}

// 2. Conexión a la base de datos
include 'conexion.php'; // Ahora $conn está disponible

// 3. Consulta para obtener todos los pedidos con información del usuario y detalles
// MODIFICACIÓN: Se añade la cláusula WHERE para excluir pedidos con Estado 'Cancelado'
$sql_pedidos = "
    SELECT 
        p.ID_Pedido,
        p.Fecha,
        p.Estado,
        p.total, -- Asumiendo que la tabla pedidos tiene una columna 'total'
        u.nombre AS nombre_usuario,
        u.apellido AS apellido_usuario,
        u.email AS email_usuario
    FROM 
        pedidos p
    JOIN 
        usuario u ON p.ID_Usuario = u.ID_Usuario
    WHERE
        p.Estado != 'Cancelado' -- <--- LÍNEA AÑADIDA PARA EXCLUIR PEDIDOS CANCELADOS
    ORDER BY 
        p.Fecha DESC;
";

$resultado_pedidos = mysqli_query($conn, $sql_pedidos);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f3f6f9; }
        .container { margin-top: 20px; }
        .table th, .table td { vertical-align: middle; }
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
                        <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="gestion_usuario.php">Gestión Usuarios</a></li>
                        <li class="nav-item"><a class="nav-link" href="gestion_pedidos.php">Gestión Pedidos</a></li> <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="mb-4">Gestión de Pedidos</h1>

        <?php if (mysqli_num_rows($resultado_pedidos) > 0): ?>
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID Pedido</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>Cliente</th>
                        <th>Email Cliente</th>
                        <th>Detalles del Pedido</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pedido = mysqli_fetch_assoc($resultado_pedidos)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pedido['ID_Pedido']); ?></td>
                            <td><?php echo htmlspecialchars($pedido['Fecha']); ?></td>
                            <td><?php echo htmlspecialchars($pedido['Estado']); ?></td>
                            <td>$<?php echo number_format(htmlspecialchars($pedido['total']), 2, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($pedido['nombre_usuario'] . ' ' . $pedido['apellido_usuario']); ?></td>
                            <td><?php echo htmlspecialchars($pedido['email_usuario']); ?></td>
                            <td>
                                <ul class="list-unstyled">
                                    <?php
                                    // Consulta para obtener los detalles de este pedido específico
                                    $sql_detalles = "
                                        SELECT 
                                            pd.Cantidad,
                                            r.nombre AS nombre_paquete,
                                            r.precio AS precio_paquete
                                        FROM 
                                            pedido_detalles pd
                                        JOIN 
                                            paquetes r ON pd.ID_Reserva = r.ID_Reserva
                                        WHERE 
                                            pd.ID_Pedido = ?;
                                    ";
                                    $stmt_detalles = mysqli_prepare($conn, $sql_detalles);
                                    mysqli_stmt_bind_param($stmt_detalles, "i", $pedido['ID_Pedido']);
                                    mysqli_stmt_execute($stmt_detalles);
                                    $resultado_detalles = mysqli_stmt_get_result($stmt_detalles);

                                    while ($detalle = mysqli_fetch_assoc($resultado_detalles)):
                                    ?>
                                        <li>
                                            <?php echo htmlspecialchars($detalle['nombre_paquete']); ?> (x<?php echo htmlspecialchars($detalle['Cantidad']); ?>) - $<?php echo number_format($detalle['precio_paquete'] * $detalle['Cantidad'], 2, ',', '.'); ?>
                                        </li>
                                    <?php endwhile; ?>
                                    <?php mysqli_stmt_close($stmt_detalles); ?>
                                </ul>
                            </td>
                            <td>
                                <a href="cambiar_estado_pedido.php?id=<?php echo htmlspecialchars($pedido['ID_Pedido']); ?>" class="btn btn-info btn-sm">Ver/Cambiar Estado</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                No hay pedidos registrados en el sistema.
            </div>
        <?php endif; ?>

    </div>

    <footer class="text-center mt-5 p-4 bg-light">
        <small>&copy; 2025 Agencia de Viajes. Todos los derechos reservados.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
mysqli_close($conn); // Cierra la conexión a la base de datos al final del script
?>