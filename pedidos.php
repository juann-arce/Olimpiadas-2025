<?php
session_start(); // Inicia la sesión

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Incluye tu archivo de conexión a la base de datos
include 'conexion.php'; // Aquí se incluye el archivo conexion.php

$usuario_id = (int)$_SESSION['usuario_id']; // Asegúrate de que el ID del usuario sea un entero

// Consulta para obtener los pedidos del usuario logueado
// Incluye el cálculo del total de cada pedido y los detalles de los paquetes
$sql = "
    SELECT 
        p.ID_Pedido, 
        p.Fecha, 
        p.Estado,
        p.Total AS TotalPedido, -- Asumiendo que la tabla 'pedidos' tiene una columna 'Total'
        GROUP_CONCAT(CONCAT(pa.nombre, ' (', pd.Cantidad, 'x $', FORMAT(pa.precio, 2), ')') SEPARATOR '<br>') AS paquetes_detalles
    FROM pedidos p
    JOIN pedido_detalles pd ON p.ID_Pedido = pd.ID_Pedido
    JOIN paquetes pa ON pd.ID_Reserva = pa.ID_Reserva
    WHERE p.ID_Usuario = ?
    GROUP BY p.ID_Pedido, p.Fecha, p.Estado, p.Total -- Agrega 'p.Total' al GROUP BY para que sea consistente
    ORDER BY p.Fecha DESC
";

// Prepara la consulta usando la conexión $conn
$stmt = mysqli_prepare($conn, $sql); 

// Verifica si la preparación de la consulta fue exitosa
if ($stmt === false) {
    die("Error al preparar la consulta: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $usuario_id); // 'i' indica que $usuario_id es un entero
mysqli_stmt_execute($stmt); 
$resultado = mysqli_stmt_get_result($stmt); // Obtiene el conjunto de resultados

// Cierra el statement preparado después de obtener los resultados
mysqli_stmt_close($stmt);

// La conexión se cerrará al final del script con mysqli_close($conn);
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
        .badge {
            font-size: 0.85em; 
            padding: 0.4em 0.7em; 
            display: inline-block; 
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

        <?php if (mysqli_num_rows($resultado) === 0): ?> 
            <div class="alert alert-info" role="alert">
                Todavía no realizaste ningún pedido.
            </div>
            <a href="index.php" class="btn btn-primary">Ver paquetes</a>
        <?php else: ?>
            <table class="table table-bordered table-striped bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Detalles del Pedido</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pedido = mysqli_fetch_assoc($resultado)): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($pedido['Fecha']))) ?></td>
                            <td><?= $pedido['paquetes_detalles'] ?></td> <td>$<?= number_format($pedido['TotalPedido'], 2, ',', '.') ?></td>
                            <td>
                                <?php
                                    $estado = htmlspecialchars($pedido['Estado']);
                                    switch ($estado) {
                                        case 'Pendiente': 
                                            echo '<span class="badge bg-secondary">Pendiente</span>';
                                            break;
                                        case 'Confirmado':
                                            echo '<span class="badge bg-info text-dark">Confirmado</span>';
                                            break;
                                        case 'Pagado':
                                            echo '<span class="badge bg-success">Pagado</span>';
                                            break;
                                        case 'Entregado': // O 'Completado', 'Finalizado'
                                            echo '<span class="badge bg-primary">Entregado</span>';
                                            break;
                                        case 'Anulado': // O 'Cancelado'
                                            echo '<span class="badge bg-danger">Anulado</span>';
                                            break;
                                        default:
                                            echo '<span class="badge bg-light text-dark">' . $estado . '</span>';
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

    <footer class="text-center mt-5 p-4 bg-light">
        <small>&copy; 2025 Agencia de Viajes. Todos los derechos reservados.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Asegúrate de cerrar la conexión al final del script
mysqli_close($conn); 
?>