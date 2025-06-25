<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: index.php"); 
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de paquete no especificado.");
}

$id = intval($_GET['id']);
if ($id <= 0) {
    die("ID de paquete inválido.");
}

$conexion = new mysqli("localhost", "root", "", "agencia");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}


$conexion->begin_transaction();
$exito_transaccion = true; 
try {
   
    $stmt_select_img = $conexion->prepare("SELECT imagen FROM paquetes WHERE ID_Reserva = ?");
    $stmt_select_img->bind_param("i", $id);
    $stmt_select_img->execute();
    $resultado = $stmt_select_img->get_result();

    if ($resultado->num_rows === 0) {
        throw new Exception("Paquete no encontrado."); 
    }

    $paquete = $resultado->fetch_assoc();
    $rutaImagen = $paquete['imagen'];
    $stmt_select_img->close();

   
    $stmt_delete_details = $conexion->prepare("DELETE FROM pedido_detalles WHERE ID_Reserva = ?"); 
    $stmt_delete_details->bind_param("i", $id);
    if (!$stmt_delete_details->execute()) {
        throw new Exception("Error al eliminar detalles de pedido: " . $stmt_delete_details->error);
    }
    $stmt_delete_details->close();

 
    $stmt_delete_paquete = $conexion->prepare("DELETE FROM paquetes WHERE ID_Reserva = ?"); 
    $stmt_delete_paquete->bind_param("i", $id);

    if (!$stmt_delete_paquete->execute()) {
        throw new Exception("Error al eliminar el paquete: " . $stmt_delete_paquete->error);
    }
    $stmt_delete_paquete->close();

    if (!empty($rutaImagen) && file_exists($rutaImagen)) {
        unlink($rutaImagen);
    }

    $conexion->commit();
    header("Location: index.php?mensaje=paquete_eliminado_exito");
    exit();

} catch (Exception $e) {
    
    $conexion->rollback();
    echo "Error en la operación de eliminación: " . $e->getMessage();
}

$conexion->close();
?>
