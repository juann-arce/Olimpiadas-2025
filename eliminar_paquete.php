<?php
if (!isset($_GET['id'])) {
    die("ID de paquete no especificado.");
}

$id = intval($_GET['id']);

$conexion = new mysqli("localhost", "root", "", "agencia");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Obtener la ruta de la imagen actual
$stmt = $conexion->prepare("SELECT imagen FROM paquetes WHERE ID_Reserva = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $stmt->close();
    $conexion->close();
    die("Paquete no encontrado.");
}

$paquete = $resultado->fetch_assoc();
$rutaImagen = $paquete['imagen'];
$stmt->close();

// Eliminar el paquete
$stmt = $conexion->prepare("DELETE FROM paquetes WHERE ID_Reserva = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Eliminar imagen si existe
    if (!empty($rutaImagen) && file_exists($rutaImagen)) {
        unlink($rutaImagen);
    }

    header("Location: index.php");
    exit();
} else {
    echo "Error al eliminar: " . $stmt->error;
}

$stmt->close();
$conexion->close();
?>
