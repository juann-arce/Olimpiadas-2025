<?php
session_start(); // Inicia la sesión

// Redirigir si no hay un usuario logueado o si es un admin
// Se asume que solo usuarios normales deben acceder al carrito
if (!isset($_SESSION['usuario_id']) || (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin')) {
    header("Location: login.php");
    exit();
}

// Conexión a base de datos
include 'conexion.php'; // <--- AÑADIDO ESTO (Ahora $conn está disponible)

// Eliminar producto del carrito
if (isset($_GET['eliminar'])) {
    $idEliminar = (int)$_GET['eliminar'];
    if (isset($_SESSION['carrito'][$idEliminar])) {
        unset($_SESSION['carrito'][$idEliminar]);
    }
    // Redirige después de eliminar para limpiar la URL
    header('Location: carrito.php');
    exit();
}

// Obtener IDs del carrito para consulta
$ids = isset($_SESSION['carrito']) ? array_keys($_SESSION['carrito']) : [];

$paquetes_carrito = [];
$total_carrito_final = 0; // Inicializar el total del carrito aquí

if (!empty($ids)) {
    // Crear una cadena de marcadores de posición para la consulta IN
    $ids_placeholders = implode(',', array_fill(0, count($ids), '?'));

    // Preparar la consulta usando $conn
    $stmt = mysqli_prepare($conn, "SELECT ID_Reserva, nombre, precio FROM paquetes WHERE ID_Reserva IN ($ids_placeholders)"); // <--- USANDO $conn

    // Construir la cadena de tipos dinámicamente ('i' por cada ID)
    $types = str_repeat('i', count($ids));
    
    // Bindear los parámetros (necesita ser por referencia para call_user_func_array)
    // Esto es un poco más complejo con mysqli procedimental, se usa call_user_func_array
    $params = array_merge([$stmt, $types], array_values($ids));
    call_user_func_array('mysqli_stmt_bind_param', refer_to_each_element($params)); // <--- USANDO call_user_func_array y función auxiliar

    mysqli_stmt_execute($stmt); // <--- USANDO mysqli_stmt_execute

    $resultado = mysqli_stmt_get_result($stmt); // <--- USANDO mysqli_stmt_get_result

    while ($fila = mysqli_fetch_assoc($resultado)) { // <--- USANDO mysqli_fetch_assoc
        $paquetes_carrito[$fila['ID_Reserva']] = $fila;
    }

    mysqli_stmt_close($stmt); // <--- USANDO mysqli_stmt_close

    // Calcular el total final del carrito
    foreach ($paquetes_carrito as $id => $paquete) {
        // Asegúrate de que la cantidad exista en la sesión, si no, usa 0 o 1
        $cantidad = $_SESSION['carrito'][$id]['cantidad'] ?? 0;
        $subtotal = $paquete['precio'] * $cantidad;
        $total_carrito_final += $subtotal;
    }
}

// Guardar el total en la sesión antes de redirigir al formulario de pago
// Esto es importante para que formulario_pago.php pueda mostrar el total.
$_SESSION['total_carrito'] = $total_carrito_final;

mysqli_close($conn); // <--- Cierra la conexión a la base de datos al final del script

/**
 * Función auxiliar para obtener referencias a los elementos de un array
 * Necesario para mysqli_stmt_bind_param con parámetros dinámicos.
 * @param array $arr El array del cual obtener las referencias.
 * @return array Un nuevo array con referencias a los elementos originales.
 */
function refer_to_each_element($arr) {
    $refs = [];
    foreach ($arr as $key => $value) {
        $refs[$key] = &$arr[$key];
    }
    return $refs;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
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
    <div class="container mt-5">
        <h1 class="mb-4">Carrito de Compras</h1>

        <?php if (empty($paquetes_carrito)): ?>
            <div class="alert alert-info">Tu carrito está vacío.</div>
            <a href="index.php" class="btn btn-primary">Volver a paquetes</a>
        <?php else: ?>
            <form method="POST" action="formulario_pago.php"> 
                <table class="table table-bordered bg-white">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Precio Unitario</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($paquetes_carrito as $id => $paquete):
                            $cantidad = $_SESSION['carrito'][$id]['cantidad'] ?? 0; // Fallback por si acaso
                            $subtotal = $paquete['precio'] * $cantidad;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($paquete['nombre']) ?></td>
                            <td>$<?= number_format($paquete['precio'], 2, ',', '.') ?></td>
                            <td><?= $cantidad ?></td>
                            <td>$<?= number_format($subtotal, 2, ',', '.') ?></td>
                            <td>
                                <a href="carrito.php?eliminar=<?= $id ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('¿Eliminar este producto del carrito?')">
                                   Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td colspan="2"><strong>$<?= number_format($total_carrito_final, 2, ',', '.') ?></strong></td>
                        </tr>
                    </tbody>
                </table>

                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Seguir comprando</a>
                    <button type="submit" class="btn btn-success">Confirmar y Pagar</button> 
                </div>
            </form>
        <?php endif; ?>
    </div>

    <footer class="text-center mt-5 p-4 bg-light">
        <small>&copy; 2025 Agencia de Viajes. Todos los derechos reservados.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>