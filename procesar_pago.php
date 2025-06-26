<?php
session_start();

// Redirigir si no hay un usuario logueado o si es un admin
if (!isset($_SESSION['usuario_id']) || (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin')) {
    header("Location: login.php");
    exit();
}

// Incluye tu archivo de conexión a la base de datos
include 'conexion.php'; // <--- AÑADIDO ESTO (Ahora $conn está disponible)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Simulación de procesamiento de pago (falso)
    // En un entorno real, aquí se integrarían con una pasarela de pago real.
    // sleep(1); // Simular un pequeño retraso de procesamiento (descomentar para probar)

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
        mysqli_begin_transaction($conn); // <--- USANDO mysqli_begin_transaction

        try {
            // 4. Insertar el nuevo pedido en la tabla 'pedidos'
            $fecha_actual = date('Y-m-d H:i:s');
            $estado_inicial = 'Pendiente'; // Estado inicial del pedido

            $stmt_pedido = mysqli_prepare($conn, "INSERT INTO pedidos (ID_Usuario, Fecha, Estado, Total) VALUES (?, ?, ?, ?)"); // <--- USANDO mysqli_prepare
            
            if ($stmt_pedido === false) {
                throw new Exception("Error al preparar el statement del pedido: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt_pedido, "isss", $usuario_id, $fecha_actual, $estado_inicial, $total_carrito); // <--- USANDO mysqli_stmt_bind_param
            
            if (!mysqli_stmt_execute($stmt_pedido)) { // <--- USANDO mysqli_stmt_execute
                throw new Exception("Error al crear el pedido principal: " . mysqli_stmt_error($stmt_pedido)); // <--- USANDO mysqli_stmt_error
            }
            $pedido_id = mysqli_stmt_insert_id($stmt_pedido); // <--- USANDO mysqli_stmt_insert_id
            mysqli_stmt_close($stmt_pedido); // <--- USANDO mysqli_stmt_close

            // 5. Insertar los detalles del pedido en 'pedido_detalles'
            $stmt_detalle = mysqli_prepare($conn, "INSERT INTO pedido_detalles (ID_Pedido, ID_Reserva, Cantidad, Precio_Unitario_Al_Comprar) VALUES (?, ?, ?, ?)");
            if ($stmt_detalle === false) {
                throw new Exception("Error al preparar el statement de detalles: " . mysqli_error($conn));
            }

            foreach ($carrito as $id_paquete => $item) {
                $cantidad = $item['cantidad'];
                
                // Obtener el precio unitario del paquete de la base de datos para mayor seguridad
                $stmt_precio = mysqli_prepare($conn, "SELECT precio FROM paquetes WHERE ID_Reserva = ?");
                if ($stmt_precio === false) {
                    throw new Exception("Error al preparar el statement de precio: " . mysqli_error($conn));
                }
                mysqli_stmt_bind_param($stmt_precio, "i", $id_paquete);
                mysqli_stmt_execute($stmt_precio);
                $res_precio = mysqli_stmt_get_result($stmt_precio);
                $fila_precio = mysqli_fetch_assoc($res_precio);
                $precio_unitario = $fila_precio['precio'];
                mysqli_stmt_close($stmt_precio);

                mysqli_stmt_bind_param($stmt_detalle, "iiid", $pedido_id, $id_paquete, $cantidad, $precio_unitario);
                
                if (!mysqli_stmt_execute($stmt_detalle)) {
                    throw new Exception("Error al insertar detalle del pedido para paquete ID " . $id_paquete . ": " . mysqli_stmt_error($stmt_detalle));
                }
            }
            mysqli_stmt_close($stmt_detalle);

            // Si todo fue bien, confirmar la transacción
            mysqli_commit($conn); // <--- USANDO mysqli_commit

            // 6. Vaciar el carrito de la sesión
            unset($_SESSION['carrito']);
            unset($_SESSION['total_carrito']);

            // Redirigir a la página de confirmación de éxito
            header("Location: confirmacion_compra.php?status=success");
            exit();

        } catch (Exception $e) {
            // Si algo falla, revertir la transacción
            mysqli_rollback($conn); // <--- USANDO mysqli_rollback
            // Redirigir a la página de confirmación de error
            error_log("Error al procesar compra: " . $e->getMessage()); // Para depuración
            $_SESSION['error_compra'] = "Hubo un error al procesar tu compra. Por favor, inténtalo de nuevo.";
            header("Location: confirmacion_compra.php?status=error");
            exit();
        }

    } else {
        // Redirigir a la página de confirmación de error (si el pago real fallara)
        $_SESSION['error_compra'] = "El pago no pudo ser procesado. Por favor, inténtalo de nuevo o contacta a soporte.";
        header("Location: confirmacion_compra.php?status=error");
        exit();
    }
} else {
    // Si alguien intenta acceder directamente a procesar_pago.php sin POST, redirigir al carrito
    header("Location: carrito.php");
    exit();
}

// Cierra la conexión a la base de datos al final del script si no se ha salido ya
mysqli_close($conn); 
?>