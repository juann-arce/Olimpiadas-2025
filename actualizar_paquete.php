<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}
$conn = new mysqli("localhost", "root", "", "agencia");
if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}

$id = $_POST['id'];
$nombre = $_POST['nombre'];
$descripcion = $_POST['descripcion'];
$destino = $_POST['destino'];
$precio = $_POST['precio'];

$sql = "UPDATE paquetes SET nombre=?, descripcion=?, destino=?, precio=? WHERE ID_Reserva=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssdi", $nombre, $descripcion, $destino, $precio, $id);

$stmt->execute();
$stmt->close();
$conn->close();

header("Location: index.php");
?>
