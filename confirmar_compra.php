<?php
session_start();

// 1. Verificación de Autenticación y Existencia de Carrito
// Redirigir si el usuario no está logueado
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    // Puedes redirigir a la página de login o mostrar un mensaje
    header("Location: login.php?error=no_logueado");
    exit();
}

$usuario_id = (int)$_SESSION['usuario_id']; // Aseguramos que sea un entero
$carrito = $_SESSION['carrito'] ?? [];

// Redirigir si el carrito está vacío
if (empty($carrito)) {
    header("Location: carrito.php?error=carrito_vacio");
    exit();
}

// 2. Conexión a la base de datos
include 'conexion.php'; // <--- AÑADIDO ESTO (Ahora $conn está disponible)

// Iniciar una transacción para asegurar la atomicidad de la operación
mysqli_begin_transaction($conn); // <--- USANDO mysqli_begin_transaction($conn)

try {
    // 3. Verificar que el usuario existe en la tabla 'usuario' (seguridad y validación de FK)
    // Usamos prepared statement para mayor seguridad
    $stmt_check_user = mysqli_prepare($conn, "SELECT ID_Usuario FROM usuario WHERE ID_Usuario = ?"); // <--- USANDO $conn
    mysqli_stmt_bind_param($stmt_check_user, "i", $usuario_id); // <--- USANDO mysqli_stmt_bind_param
    mysqli_stmt_execute($stmt_check_user); // <--- USANDO mysqli_stmt_execute
    $resultado_user = mysqli_stmt_get_result($stmt_check_user); // <--- USANDO mysqli_stmt_get_result

    if (mysqli_num_rows($resultado_user) === 0) { // <--- USANDO mysqli_num_rows
        throw new Exception("El usuario con ID $usuario_id no existe en la base de datos o es inválido.");
    }
    mysqli_stmt_close($stmt_check_user); // <--- USANDO mysqli_stmt_close

    // 4. Calcular el total del pedido antes de insertarlo
    $total_pedido = 0;
    foreach ($carrito as $id_reserva => $datos) {
        $stmt_precio_paquete = mysqli_prepare($conn, "SELECT precio FROM paquetes WHERE ID_Reserva = ?"); // <--- USANDO $conn
        mysqli_stmt_bind_param($stmt_precio_paquete, "i", $id_reserva); // <--- USANDO mysqli_stmt_bind_param
        mysqli_stmt_execute($stmt_precio_paquete); // <--- USANDO mysqli_stmt_execute
        $resultado_precio = mysqli_stmt_get_result($stmt_precio_paquete); // <--- USANDO mysqli_stmt_get_result
        $paquete_db = mysqli_fetch_assoc($resultado_precio); // <--- USANDO mysqli_fetch_assoc
        mysqli_stmt_close($stmt_precio_paquete); // <--- USANDO mysqli_stmt_close

        if (!$paquete_db) {
            throw new Exception("El paquete con ID $id_reserva no fue encontrado en la base de datos.");
        }
        $total_pedido += ($paquete_db['precio'] * $datos['cantidad']);
    }

    // 5. Insertar el encabezado del pedido en la tabla 'pedidos'
    $stmt_pedido = mysqli_prepare($conn, "INSERT INTO pedidos (ID_Usuario, total, Fecha, Estado) VALUES (?, ?, NOW(), ?)"); // <--- USANDO $conn
    $estado_inicial = 'pendiente'; 
    mysqli_stmt_bind_param($stmt_pedido, "ids", $usuario_id, $total_pedido, $estado_inicial); // <--- USANDO mysqli_stmt_bind_param

    if (!mysqli_stmt_execute($stmt_pedido)) { // <--- USANDO mysqli_stmt_execute
        throw new Exception("Error al guardar el pedido principal: " . mysqli_error($conn)); // <--- USANDO mysqli_error($conn)
    }
    $pedido_id = mysqli_insert_id($conn); // <--- USANDO mysqli_insert_id($conn)
    mysqli_stmt_close($stmt_pedido); // <--- USANDO mysqli_stmt_close

    // 6. Insertar cada detalle del paquete en la tabla 'pedido_detalles'
    $stmt_detalle = mysqli_prepare($conn, "INSERT INTO pedido_detalles (ID_Pedido, ID_Reserva, Cantidad) VALUES (?, ?, ?)"); // <--- USANDO $conn

    foreach ($carrito as $id_reserva => $datos) {
        $cantidad = (int)$datos['cantidad']; 

        mysqli_stmt_bind_param($stmt_detalle, "iii", $pedido_id, $id_reserva, $cantidad); // <--- USANDO mysqli_stmt_bind_param
        if (!mysqli_stmt_execute($stmt_detalle)) { // <--- USANDO mysqli_stmt_execute
            throw new Exception("Error al guardar el detalle del paquete con ID_Reserva $id_reserva: " . mysqli_error($conn)); // <--- USANDO mysqli_error($conn)
        }
    }
    mysqli_stmt_close($stmt_detalle); // <--- USANDO mysqli_stmt_close

    // Si todo salió bien, confirmar la transacción
    mysqli_commit($conn); // <--- USANDO mysqli_commit($conn)

    // 7. Limpiar el carrito de la sesión
    unset($_SESSION['carrito']);

    // 8. Redirigir a la página de confirmación con un mensaje de éxito
    header("Location: confirmacion_compra.php?status=success"); // Redirige a confirmacion_compra.php
    exit();

} catch (Exception $e) {
    // Si algo falla, revertir la transacción
    mysqli_rollback($conn); // <--- USANDO mysqli_rollback($conn)

    // Loguear el error y redirigir a la página de confirmación con error
    error_log("Error en confirmar_compra.php: " . $e->getMessage()); 
    header("Location: confirmacion_compra.php?status=error"); // Redirige a confirmacion_compra.php
    exit(); // Importante para detener la ejecución
} finally {
    // Asegurarse de que la conexión se cierre
    if ($conn) { // <--- Verificar $conn
        mysqli_close($conn); // <--- USANDO mysqli_close($conn)
    }
}
?>