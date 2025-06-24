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
$conexion = new mysqli("localhost", "root", "", "agencia");
if ($conexion->connect_error) {
    // En un entorno de producción, podrías loguear el error y mostrar un mensaje genérico al usuario
    die("Error de conexión a la base de datos: " . $conexion->connect_error);
}

// Iniciar una transacción para asegurar la atomicidad de la operación
$conexion->begin_transaction();

try {
    // 3. Verificar que el usuario existe en la tabla 'usuario' (seguridad y validación de FK)
    // Usamos prepared statement para mayor seguridad
    $stmt_check_user = $conexion->prepare("SELECT ID_Usuario FROM usuario WHERE ID_Usuario = ?");
    $stmt_check_user->bind_param("i", $usuario_id);
    $stmt_check_user->execute();
    $resultado_user = $stmt_check_user->get_result();

    if ($resultado_user->num_rows === 0) {
        throw new Exception("El usuario con ID $usuario_id no existe en la base de datos o es inválido.");
    }
    $stmt_check_user->close();

    // 4. Calcular el total del pedido antes de insertarlo
    // Esto es crucial si la tabla 'pedidos' tiene una columna 'total'
    $total_pedido = 0;
    foreach ($carrito as $id_reserva => $datos) {
        // Necesitamos el precio actual del paquete desde la BD,
        // ya que el precio en sesión podría no estar actualizado
        $stmt_precio_paquete = $conexion->prepare("SELECT precio FROM paquetes WHERE ID_Reserva = ?");
        $stmt_precio_paquete->bind_param("i", $id_reserva);
        $stmt_precio_paquete->execute();
        $resultado_precio = $stmt_precio_paquete->get_result();
        $paquete_db = $resultado_precio->fetch_assoc();
        $stmt_precio_paquete->close();

        if (!$paquete_db) {
            throw new Exception("El paquete con ID $id_reserva no fue encontrado en la base de datos.");
        }
        $total_pedido += ($paquete_db['precio'] * $datos['cantidad']);
    }

    // 5. Insertar el encabezado del pedido en la tabla 'pedidos'
    // Asumiendo que 'total' y 'metodo_pago' se pueden añadir aquí o en un paso posterior
    // También asumo que 'Estado' en 'pedidos' tiene un valor predeterminado como 'pendiente'
    // Si 'Estado' no tiene default, debes especificarlo aquí: INSERT INTO pedidos (ID_Usuario, Fecha, Estado, total)
    $stmt_pedido = $conexion->prepare("INSERT INTO pedidos (ID_Usuario, total, Fecha, Estado) VALUES (?, ?, NOW(), ?)"); // Agregado total, Fecha, Estado
    // Si tu columna 'Estado' en 'pedidos' tiene un DEFAULT 'pendiente', puedes simplificar la consulta:
    // $stmt_pedido = $conexion->prepare("INSERT INTO pedidos (ID_Usuario, total, Fecha) VALUES (?, ?, NOW())");
    $estado_inicial = 'pendiente'; // Define el estado inicial del pedido
    $stmt_pedido->bind_param("ids", $usuario_id, $total_pedido, $estado_inicial); // 'i' para int, 'd' para double/decimal, 's' para string

    if (!$stmt_pedido->execute()) {
        throw new Exception("Error al guardar el pedido principal: " . $stmt_pedido->error);
    }
    $pedido_id = $stmt_pedido->insert_id; // Obtiene el ID del pedido recién insertado
    $stmt_pedido->close();

    // 6. Insertar cada detalle del paquete en la tabla 'pedido_detalles'
    $stmt_detalle = $conexion->prepare("INSERT INTO pedido_detalles (ID_Pedido, ID_Reserva, Cantidad) VALUES (?, ?, ?)");

    foreach ($carrito as $id_reserva => $datos) {
        $cantidad = (int)$datos['cantidad']; // Aseguramos que sea entero
        // No necesitamos el precio unitario de venta aquí si ya lo hemos usado para calcular el total del pedido
        // Pero si tu tabla pedido_detalles tuviera una columna 'precio_unitario_venta', deberías añadirla aquí

        $stmt_detalle->bind_param("iii", $pedido_id, $id_reserva, $cantidad);
        if (!$stmt_detalle->execute()) {
            throw new Exception("Error al guardar el detalle del paquete con ID_Reserva $id_reserva: " . $stmt_detalle->error);
        }
    }
    $stmt_detalle->close();

    // Si todo salió bien, confirmar la transacción
    $conexion->commit();

    // 7. Limpiar el carrito de la sesión
    unset($_SESSION['carrito']);

    // 8. Redirigir a la página de pedidos con un mensaje de éxito
    header("Location: pedidos.php?success=compra_confirmada");
    exit();

} catch (Exception $e) {
    // Si algo falla, revertir la transacción
    $conexion->rollback();

    // Puedes redirigir a una página de error o mostrar el mensaje
    error_log("Error en confirmar_compra.php: " . $e->getMessage()); // Loguear el error para depuración
    die("Ha ocurrido un error al procesar tu compra. Por favor, inténtalo de nuevo más tarde. Detalles: " . $e->getMessage());
} finally {
    // Asegurarse de que la conexión se cierre
    if ($conexion) {
        $conexion->close();
    }
}
?>