<?php
session_start(); // Inicia la sesión

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Incluye tu archivo de conexión a la base de datos
include 'conexion.php'; // <--- AÑADIDO ESTO (Ahora $conn está disponible)

$usuario_id = $_SESSION['usuario_id'];

// Consulta para obtener los pedidos del usuario logueado
// Incluye el cálculo del total de cada pedido y los detalles de los paquetes
$sql = "
SELECT 
    p.ID_Pedido, 
    p.Fecha, 
    p.Estado,
    p.total AS TotalPedido, -- Asumiendo que la tabla 'pedidos' tiene una columna 'total'
    GROUP_CONCAT(CONCAT(pa.nombre, ' (', pd.Cantidad, 'x $', FORMAT(pa.precio, 2), ')') SEPARATOR '<br>') AS paquetes_detalles
FROM pedidos p
JOIN pedido_detalles pd ON p.ID_Pedido = pd.ID_Pedido
JOIN paquetes pa ON pd.ID_Reserva = pa.ID_Reserva
WHERE p.ID_Usuario = ?
GROUP BY p.ID_Pedido, p.Fecha, p.Estado, p.total -- Agrega 'p.total' al GROUP BY
ORDER BY p.Fecha DESC
";

// Prepara la consulta usando la conexión $conn
$stmt = mysqli_prepare($conn, $sql); // <--- USANDO mysqli_prepare($conn, ...)
mysqli_stmt_bind_param($stmt, "i", $usuario_id); // <--- USANDO mysqli_stmt_bind_param
mysqli_stmt_execute($stmt); // <--- USANDO mysqli_stmt_execute
$resultado = mysqli_stmt_get_result($stmt); // <--- USANDO mysqli_stmt_get_result
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - Agencia de Viajes</title>
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
                        <li class="nav-item"><a class="nav-link" href="gestion_pedidos.php">Gestión Pedidos</a></li>
                        <li class="nav-item"><a class="nav-link" href="gestion_usuarios.php">Gestión Usuarios</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Cerrar Sesión (Admin)</a></li>
                    <?php elseif (isset($_SESSION['usuario_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="carrito.php">Carrito</a></li>
                        <li class="nav-item"><a class="nav-link" href="pedidos.php">Mis Pedidos</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Iniciar Sesión</a></li>
                        <li class="nav-item"><a class="nav-link" href="registro.php">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Mis Pedidos</h2>

        <?php if (mysqli_num_rows($resultado) > 0): ?> <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Detalles del Pedido</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($fila = mysqli_fetch_assoc($resultado)): ?> <tr>
                            <td><?= htmlspecialchars($fila['Fecha']) ?></td>
                            <td><?= $fila['paquetes_detalles'] ?></td> <td>$<?= number_format($fila['TotalPedido'], 2, ',', '.') ?></td>
                            <td>
                                <?php
                                    $estado = htmlspecialchars($fila['Estado']);
                                    if ($estado === 'pendiente') { // Asumiendo un estado 'pendiente' inicial
                                        echo '<span class="badge bg-secondary">Pendiente</span>';
                                    } elseif ($estado === 'confirmado') {
                                        echo '<span class="badge bg-info text-dark">Confirmado</span>'; // Estado para cuando se ha revisado pero no pagado/enviado
                                    } elseif ($estado === 'pagado') { // O 'procesando'
                                        echo '<span class="badge bg-primary">Pagado</span>';
                                    } elseif ($estado === 'completado') { // Cuando el servicio ya se brindó
                                        echo '<span class="badge bg-success">Completado</span>';
                                    } elseif ($estado === 'cancelado') {
                                        echo '<span class="badge bg-danger">Cancelado</span>';
                                    } else {
                                        echo '<span class="badge bg-light text-dark">' . $estado . '</span>';
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                No tienes pedidos realizados aún.
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
mysqli_stmt_close($stmt); // Cierra el statement preparado
mysqli_close($conn); // <--- Cierra la conexión a la base de datos al final del script
?>