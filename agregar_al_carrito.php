<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_reserva']) && isset($_POST['cantidad'])) {
    $id_reserva = (int)$_POST['id_reserva'];
    $cantidad = (int)$_POST['cantidad'];

    if ($cantidad < 1) {
        $cantidad = 1;
    }

    // Conexión a la base de datos
    $conexion = new mysqli("localhost", "root", "", "agencia");
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Validar que el paquete existe
    $stmt = $conexion->prepare("SELECT nombre, precio FROM paquetes WHERE ID_Reserva = ?");
    $stmt->bind_param("i", $id_reserva);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $paquete = $resultado->fetch_assoc();

        // Inicializar carrito si no existe
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        // Si el paquete ya está en el carrito, sumamos la cantidad
        if (isset($_SESSION['carrito'][$id_reserva])) {
            $_SESSION['carrito'][$id_reserva]['cantidad'] += $cantidad;
        } else {
            // Si no está, lo agregamos
            $_SESSION['carrito'][$id_reserva] = [
                'nombre' => $paquete['nombre'],
                'precio' => $paquete['precio'],
                'cantidad' => $cantidad
            ];
        }

        $stmt->close();
        $conexion->close();

        // Redirigir a la página del carrito
        header("Location: carrito.php");
        exit();
    } else {
        // Paquete no encontrado, redirigir a paquetes con error (podés mejorar esto)
        $stmt->close();
        $conexion->close();
        header("Location: paquetes.php?error=paquete_no_encontrado");
        exit();
    }
} else {
    // Datos incompletos, redirigir a paquetes
    header("Location: paquetes.php");
    exit();
}