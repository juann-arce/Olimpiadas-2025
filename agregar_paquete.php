<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre'];
  $descripcion = $_POST['descripcion'];
  $precio = $_POST['precio'];
  $tipo = $_POST['tipo'];
  $destino = $_POST['destino'];

  // Procesar imagen
  $imagenNombre = $_FILES['imagen']['name'];
  $imagenTmp = $_FILES['imagen']['tmp_name'];
  $rutaDestino = 'imagenes/' . basename($imagenNombre);

  // Crear carpeta si no existe
  if (!file_exists('imagenes')) {
    mkdir('imagenes', 0777, true);
  }

  if (move_uploaded_file($imagenTmp, $rutaDestino)) {
    $conexion = new mysqli("localhost", "root", "", "agencia");

    if ($conexion->connect_error) {
      die("Conexión fallida: " . $conexion->connect_error);
    }

    $stmt = $conexion->prepare("INSERT INTO paquetes (nombre, descripcion, precio, tipo, destino, imagen) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisss", $nombre, $descripcion, $precio, $tipo, $destino, $rutaDestino);

    if ($stmt->execute()) {
      header("Location: index.php");
      exit();
    } else {
      echo "Error al guardar en la base de datos: " . $stmt->error;
    }

    $stmt->close();
    $conexion->close();
  } else {
    echo "Error al subir la imagen.";
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Paquete</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <h2>Agregar Nuevo Paquete Turístico</h2>
    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-control" required></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Precio</label>
        <input type="number" name="precio" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Tipo</label>
        <select name="tipo" class="form-select">
          <option value="estadía">Estadía</option>
          <option value="pasaje">Pasaje</option>
          <option value="auto">Auto</option>
          <option value="completo">Completo</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Destino</label>
        <input type="text" name="destino" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Imagen (Archivo)</label>
        <input type="file" name="imagen" class="form-control" accept="image/*" required>
      </div>
      <button type="submit" class="btn btn-primary">Agregar</button>
      <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>
</body>
</html>
