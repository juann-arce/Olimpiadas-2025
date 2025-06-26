<?php
session_start();

// 1. Verificación de Autenticación y Autorización
// Solo los administradores pueden acceder a esta página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') { // <--- Asegúrate de usar 'usuario_id' también para consistencia
    header("Location: index.php"); 
    exit();
}

// 2. Validación de ID del paquete
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de paquete no especificado.");
}

$id = intval($_GET['id']);
if ($id <= 0) {
    die("ID de paquete inválido.");
}

// 3. Conexión a la base de datos
include 'conexion.php'; // <--- AÑADIDO ESTO (Ahora $conn está disponible)

// Iniciar una transacción para asegurar la atomicidad de la operación
mysqli_begin_transaction($conn); // <--- USANDO mysqli_begin_transaction($conn)

try {
    // 4. Obtener la ruta de la imagen del paquete antes de eliminarlo
    $stmt_select_img = mysqli_prepare($conn, "SELECT imagen FROM paquetes WHERE ID_Reserva = ?"); // <--- USANDO $conn
    mysqli_stmt_bind_param($stmt_select_img, "i", $id); // <--- USANDO mysqli_stmt_bind_param
    mysqli_stmt_execute($stmt_select_img); // <--- USANDO mysqli_stmt_execute
    $resultado = mysqli_stmt_get_result($stmt_select_img); // <--- USANDO mysqli_stmt_get_result

    if (mysqli_num_rows($resultado) === 0) { // <--- USANDO mysqli_num_rows
        throw new Exception("Paquete no encontrado."); 
    }

    $paquete = mysqli_fetch_assoc($resultado); // <--- USANDO mysqli_fetch_assoc
    $rutaImagen = $paquete['imagen'];
    mysqli_stmt_close($stmt_select_img); // <--- USANDO mysqli_stmt_close

    // 5. Eliminar registros relacionados en 'pedido_detalles'
    // Esto es crucial si 'ID_Reserva' en 'pedido_detalles' es una FK que no permite ON DELETE CASCADE
    $stmt_delete_details = mysqli_prepare($conn, "DELETE FROM pedido_detalles WHERE ID_Reserva = ?"); // <--- USANDO $conn
    mysqli_stmt_bind_param($stmt_delete_details, "i", $id); // <--- USANDO mysqli_stmt_bind_param
    if (!mysqli_stmt_execute($stmt_delete_details)) { // <--- USANDO mysqli_stmt_execute
        throw new Exception("Error al eliminar detalles de pedido relacionados: " . mysqli_error($conn)); // <--- USANDO mysqli_error($conn)
    }
    mysqli_stmt_close($stmt_delete_details); // <--- USANDO mysqli_stmt_close

    // 6. Eliminar el paquete de la tabla 'paquetes'
    $stmt_delete_paquete = mysqli_prepare($conn, "DELETE FROM paquetes WHERE ID_Reserva = ?"); // <--- USANDO $conn
    mysqli_stmt_bind_param($stmt_delete_paquete, "i", $id); // <--- USANDO mysqli_stmt_bind_param

    if (!mysqli_stmt_execute($stmt_delete_paquete)) { // <--- USANDO mysqli_stmt_execute
        throw new Exception("Error al eliminar el paquete: " . mysqli_error($conn)); // <--- USANDO mysqli_error($conn)
    }
    mysqli_stmt_close($stmt_delete_paquete); // <--- USANDO mysqli_stmt_close

    // 7. Eliminar el archivo de imagen físico del servidor
    if (!empty($rutaImagen) && file_exists($rutaImagen)) {
        unlink($rutaImagen);
    }

    // 8. Confirmar la transacción si todo fue exitoso
    mysqli_commit($conn); // <--- USANDO mysqli_commit($conn)
    header("Location: index.php?mensaje=paquete_eliminado_exito"); // Redirige con mensaje de éxito
    exit();

} catch (Exception $e) {
    // 9. Revertir la transacción en caso de error
    mysqli_rollback($conn); // <--- USANDO mysqli_rollback($conn)
    error_log("Error al eliminar paquete (ID: $id): " . $e->getMessage()); // Loguear el error
    echo "Error en la operación de eliminación: " . htmlspecialchars($e->getMessage()); // Mostrar un mensaje amigable
} finally {
    // 10. Cerrar la conexión a la base de datos
    if ($conn) { // <--- Asegurarse de que $conn existe
        mysqli_close($conn); // <--- USANDO mysqli_close($conn)
    }
}
?>