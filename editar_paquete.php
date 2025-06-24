<?php
// Conexión
$conexion = new mysqli("localhost", "root", "", "agencia");
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Obtener ID del paquete
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $tipo = $_POST['tipo'];
    $destino = $_POST['destino'];
    $imagenActual = $_POST['imagen_actual'];

    // Ver si se subió una imagen nueva
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagenNombre = $_FILES['imagen']['name'];
        $imagenTmp = $_FILES['imagen']['tmp_name'];
        $rutaDestino = 'imagenes/' . basename($imagenNombre);

        if (!file_exists('imagenes')) {
            mkdir('imagenes', 0777, true);
        }

        move_uploaded_file($imagenTmp, $rutaDestino);
    } else {
        $rutaDestino = $imagenActual; // mantener imagen anterior
    }

    // Actualizar en la base de datos
    $stmt = $conexion->prepare("UPDATE paquetes SET nombre=?, descripcion=?, precio=?, tipo=?, destino=?, imagen=? WHERE ID_Reserva=?");
    $stmt->bind_param("ssisssi", $nombre, $descripcion, $precio, $tipo, $destino, $rutaDestino, $id);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error al actualizar: " . $stmt->error;
    }

    $stmt->close();
}

// Cargar datos actuales del paquete
$stmt = $conexion->prepare("SELECT * FROM paquetes WHERE ID_Reserva=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Paquete no encontrado.";
    exit();
}

$paquete = $resultado->fetch_assoc();
$stmt->close();
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Paquete</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <h2>Editar Paquete Turístico</h2>
    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($paquete['nombre']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-control" required><?= htmlspecialchars($paquete['descripcion']) ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Precio</label>
        <input type="number" name="precio" class="form-control" required value="<?= $paquete['precio'] ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Tipo</label>
        <select name="tipo" class="form-select">
          <?php
            $tipos = ['estadía', 'pasaje', 'auto', 'completo'];
            foreach ($tipos as $t) {
              $selected = ($paquete['tipo'] === $t) ? 'selected' : '';
              echo "<option value=\"$t\" $selected>$t</option>";
            }
          ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Destino</label>
        <input type="text" name="destino" class="form-control" required value="<?= htmlspecialchars($paquete['destino']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Imagen actual</label><br>
        <?php if (!empty($paquete['imagen']) && file_exists($paquete['imagen'])): ?>
          <img src="<?= htmlspecialchars($paquete['imagen']) ?>" alt="Imagen actual" style="height: 150px; object-fit: cover;">
        <?php else: ?>
          <p>No hay imagen.</p>
        <?php endif; ?>
      </div>
      <div class="mb-3">
        <label class="form-label">Nueva imagen (opcional)</label>
        <input type="file" name="imagen" class="form-control" accept="image/*">
        <input type="hidden" name="imagen_actual" value="<?= htmlspecialchars($paquete['imagen']) ?>">
      </div>
      <button type="submit" class="btn btn-primary">Guardar cambios</button>
      <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>
</body>
</html>
