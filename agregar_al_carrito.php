<?php
session_start();

// Incluye tu archivo de conexión a la base de datos
include 'conexion.php'; // <--- AÑADIDO ESTO

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_reserva']) && isset($_POST['cantidad'])) {
    $id_reserva = (int)$_POST['id_reserva'];
    $cantidad = (int)$_POST['cantidad'];

    if ($cantidad < 1) {
        $cantidad = 1;
    }

    // No necesitas la conexión de mysqli aquí, ya la tienes de include 'conexion.php';
    // if ($conexion->connect_error) { ... } // Esto se maneja en conexion.php

    // Validar que el paquete existe
    $stmt = mysqli_prepare($conn, "SELECT nombre, precio FROM paquetes WHERE ID_Reserva = ?"); // <--- USANDO $conn
    mysqli_stmt_bind_param($stmt, "i", $id_reserva); // <--- USANDO mysqli_stmt_bind_param
    mysqli_stmt_execute($stmt); // <--- USANDO mysqli_stmt_execute
    $resultado = mysqli_stmt_get_result($stmt); // <--- USANDO mysqli_stmt_get_result

    if (mysqli_num_rows($resultado) === 1) { // <--- USANDO mysqli_num_rows
        $paquete = mysqli_fetch_assoc($resultado); // <--- USANDO mysqli_fetch_assoc

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

        mysqli_stmt_close($stmt); // <--- USANDO mysqli_stmt_close
        mysqli_close($conn); // <--- IMPORTANTE: Cierra la conexión al final del script

        // Redirigir a la página del carrito
        header("Location: carrito.php");
        exit();
    } else {
        // Paquete no encontrado, redirigir a paquetes con error (podés mejorar esto)
        mysqli_stmt_close($stmt); // <--- USANDO mysqli_stmt_close
        mysqli_close($conn); // <--- IMPORTANTE: Cierra la conexión al final del script
        header("Location: index.php?error=paquete_no_encontrado"); // Cambié a index.php ya que 'paquetes.php' no fue proporcionado
        exit();
    }
} else {
    // Datos incompletos, redirigir a la página principal
    mysqli_close($conn); // <--- IMPORTANTE: Cierra la conexión al final del script, incluso si no se usó mucho
    header("Location: index.php?error=datos_incompletos"); // Cambié a index.php
    exit();
}
?>