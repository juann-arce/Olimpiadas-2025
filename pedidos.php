<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$conexion = new mysqli("localhost", "root", "", "agencia");
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$usuario_id = (int)$_SESSION['usuario_id']; 

// Obtener los pedidos y sus paquetes
$sql = "
SELECT
    p.ID_Pedido, -- Mantener en el SELECT si lo necesitas para un futuro detalle del pedido (no se muestra, pero se recupera)
    p.Fecha,
    p.Total,
    p.Estado,
    GROUP_CONCAT(CONCAT(pa.nombre, ' x', d.Cantidad) SEPARATOR '<br>') AS paquetes
FROM pedidos p
JOIN pedido_detalles d ON p.ID_Pedido = d.ID_Pedido
JOIN paquetes pa ON d.ID_Reserva = pa.ID_Reserva
WHERE p.ID_Usuario = ?
GROUP BY p.ID_Pedido, p.Fecha, p.Total, p.Estado -- Necesario agrupar por todas las columnas seleccionadas que no son agregaciones
ORDER BY p.Fecha DESC
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

// Cierra la conexión después de obtener los resultados
$stmt->close();
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
 
        .badge {
            font-size: 0.85em; 
            padding: 0.4em 0.7em; 
            display: inline-block; 
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Agencia de Viajes</a>
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

    <div class="container mt-5">
        <h2 class="mb-4">Mis Pedidos</h2>

        <?php if ($resultado->num_rows === 0): ?>
            <div class="alert alert-info">Todavía no realizaste ningún pedido.</div>
            <a href="index.php" class="btn btn-primary">Ver paquetes</a>
        <?php else: ?>
            <table class="table table-bordered bg-white">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Paquetes</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pedido = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($pedido['Fecha']))) ?></td>
                            <td><?= $pedido['paquetes'] ?></td>
                            <td>$<?= number_format($pedido['Total'], 2, ',', '.') ?></td> <td>
                                <?php
                                   
                                    $estado = htmlspecialchars($pedido['Estado']);
                                    switch ($estado) {
                                        case 'Pendiente': 
                                            echo '<span class="badge bg-secondary">Pendiente</span>';
                                            break;
                                        case 'Confirmado':
                                            echo '<span class="badge bg-warning text-dark">Confirmado</span>';
                                            break;
                                        case 'Pagado':
                                            echo '<span class="badge bg-success">Pagado</span>';
                                            break;
                                        case 'Entregado':
                                            echo '<span class="badge bg-info text-dark">Entregado</span>';
                                            break;
                                        case 'Anulado':
                                            echo '<span class="badge bg-danger">Anulado</span>';
                                            break;
                                        default:
                                            echo '<span class="badge bg-primary">' . $estado . '</span>';
                                            break;
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>