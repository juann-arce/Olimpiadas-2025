<?php
session_start();

// Redirigir si no hay un usuario logueado o si es un admin
if (!isset($_SESSION['usuario_id']) || (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin')) {
    header("Location: login.php");
    exit();
}

// Conexión a base de datos
$conexion = new mysqli("localhost", "root", "", "agencia");
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Simulación de procesamiento de pago (falso)
    // En un entorno real, aquí se integrarían con una pasarela de pago real.
    sleep(1); // Simular un pequeño retraso de procesamiento

    // Asumimos que el pago "siempre es exitoso" para esta simulación
    $pago_exitoso = true; 

    if ($pago_exitoso) {
        // 2. Obtener datos del carrito de la sesión
        $usuario_id = $_SESSION['usuario_id'];
        $carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];
        $total_carrito = isset($_SESSION['total_carrito']) ? $_SESSION['total_carrito'] : 0;

        if (empty($carrito) || $total_carrito <= 0) {
            // Si el carrito está vacío o el total es 0, redirigir al carrito con un mensaje
            $_SESSION['mensaje_carrito'] = '<div class="alert alert-warning">Tu carrito está vacío o el total es cero. No se puede procesar la compra.</div>';
            header("Location: carrito.php");
            exit();
        }

        // 3. Iniciar una transacción para asegurar la integridad de los datos
        $conexion->begin_transaction();
        $pedido_creado_exitosamente = false;

        try {
            // 4. Insertar el nuevo pedido en la tabla 'pedidos'
            $fecha_actual = date('Y-m-d H:i:s');
            $estado_inicial = 'Pendiente'; // Estado inicial del pedido

            $stmt_pedido = $conexion->prepare("INSERT INTO pedidos (ID_Usuario, Fecha, Estado, Total) VALUES (?, ?, ?, ?)");
            $stmt_pedido->bind_param("isss", $usuario_id, $fecha_actual, $estado_inicial, $total_carrito);
            
            if (!$stmt_pedido->execute()) {
                throw new Exception("Error al crear el pedido principal: " . $stmt_pedido->error);
            }
            $pedido_id = $stmt_pedido->insert_id; // Obtener el ID del pedido recién insertado
            $stmt_pedido->close();

            // 5. Insertar los detalles del pedido en 'pedido_detalles'
            foreach ($carrito as $id_paquete => $item) {
                $cantidad = $item['cantidad'];
                // Obtener el precio unitario del paquete de la base de datos para mayor seguridad
                $stmt_precio = $conexion->prepare("SELECT precio FROM paquetes WHERE ID_Reserva = ?");
                $stmt_precio->bind_param("i", $id_paquete);
                $stmt_precio->execute();
                $res_precio = $stmt_precio->get_result();
                $fila_precio = $res_precio->fetch_assoc();
                $precio_unitario = $fila_precio['precio'];
                $stmt_precio->close();

                $stmt_detalle = $conexion->prepare("INSERT INTO pedido_detalles (ID_Pedido, ID_Reserva, Cantidad, Precio_Unitario_Al_Comprar) VALUES (?, ?, ?, ?)");
                $stmt_detalle->bind_param("iiid", $pedido_id, $id_paquete, $cantidad, $precio_unitario);
                
                if (!$stmt_detalle->execute()) {
                    throw new Exception("Error al insertar detalle del pedido para paquete ID " . $id_paquete . ": " . $stmt_detalle->error);
                }
                $stmt_detalle->close();
            }

            // Si todo fue bien, confirmar la transacción
            $conexion->commit();
            $pedido_creado_exitosamente = true;

            // 6. Vaciar el carrito de la sesión
            unset($_SESSION['carrito']);
            unset($_SESSION['total_carrito']);

            // Redirigir a la página de confirmación de éxito
            header("Location: confirmacion_compra.php?status=success");
            exit();

        } catch (Exception $e) {
            // Si algo falla, revertir la transacción
            $conexion->rollback();
            // Redirigir a la página de confirmación de error
            error_log("Error al procesar compra: " . $e->getMessage()); // Para depuración
            header("Location: confirmacion_compra.php?status=error");
            exit();
        }

    } else {
        // Redirigir a la página de confirmación de error (si el pago real fallara)
        header("Location: confirmacion_compra.php?status=error");
        exit();
    }
} else {
    // Si alguien intenta acceder directamente a procesar_pago.php sin POST, redirigir al carrito
    header("Location: carrito.php");
    exit();
}

$conexion->close();
?>