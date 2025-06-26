<?php
session_start();

// Opcional: Proteger esta página para que solo admins puedan acceder
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: index.php'); // Redirige si no es admin
    exit;
}

// Incluye tu archivo de conexión a la base de datos
include 'conexion.php'; // <--- AÑADIDO ESTO

// Obtener ID del paquete, sanitizándolo
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Si se envió el formulario (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar y obtener los datos del formulario
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre'] ?? ''); // <--- USANDO $conn
    $descripcion = mysqli_real_escape_string($conn, $_POST['descripcion'] ?? ''); // <--- USANDO $conn
    $precio = floatval($_POST['precio'] ?? 0); // Convertir a float
    $tipo = mysqli_real_escape_string($conn, $_POST['tipo'] ?? ''); // <--- USANDO $conn
    $destino = mysqli_real_escape_string($conn, $_POST['destino'] ?? ''); // <--- USANDO $conn
    $imagenActual = mysqli_real_escape_string($conn, $_POST['imagen_actual'] ?? ''); // <--- USANDO $conn

    $rutaDestino = $imagenActual; // Por defecto, mantener la imagen actual

    // Ver si se subió una imagen nueva
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagenNombre = $_FILES['imagen']['name'];
        $imagenTmp = $_FILES['imagen']['tmp_name'];
        $directorioDestino = 'imagenes/';
        $nuevaRutaDestino = $directorioDestino . basename($imagenNombre);

        // Crear carpeta si no existe
        if (!file_exists($directorioDestino)) {
            mkdir($directorioDestino, 0777, true);
        }

        if (move_uploaded_file($imagenTmp, $nuevaRutaDestino)) {
            $rutaDestino = $nuevaRutaDestino; // Actualizar con la nueva ruta de imagen
            // Opcional: Eliminar la imagen antigua si es diferente y existe
            if (!empty($imagenActual) && file_exists($imagenActual) && $imagenActual !== $rutaDestino) {
                unlink($imagenActual);
            }
        } else {
            // Manejar error al subir nueva imagen
            $error_message = "Error al subir la nueva imagen.";
        }
    }

    // Actualizar en la base de datos
    // Usamos $conn para la preparación de la consulta
    $stmt = mysqli_prepare($conn, "UPDATE paquetes SET nombre=?, descripcion=?, precio=?, tipo=?, destino=?, imagen=? WHERE ID_Reserva=?"); // <--- USANDO $conn
    mysqli_stmt_bind_param($stmt, "ssisssi", $nombre, $descripcion, $precio, $tipo, $destino, $rutaDestino, $id); // <--- USANDO mysqli_stmt_bind_param

    if (mysqli_stmt_execute($stmt)) { // <--- USANDO mysqli_stmt_execute
        header("Location: index.php?mensaje=paquete_actualizado_exito");
        exit();
    } else {
        $error_message = "Error al actualizar: " . mysqli_error($conn); // <--- USANDO mysqli_error($conn)
    }

    mysqli_stmt_close($stmt); // <--- USANDO mysqli_stmt_close
}

// Cargar datos actuales del paquete para mostrar en el formulario
// Usamos $conn para la preparación de la consulta
$stmt = mysqli_prepare($conn, "SELECT * FROM paquetes WHERE ID_Reserva=?"); // <--- USANDO $conn
mysqli_stmt_bind_param($stmt, "i", $id); // <--- USANDO mysqli_stmt_bind_param
mysqli_stmt_execute($stmt); // <--- USANDO mysqli_stmt_execute
$resultado = mysqli_stmt_get_result($stmt); // <--- USANDO mysqli_stmt_get_result

if (mysqli_num_rows($resultado) === 0) { // <--- USANDO mysqli_num_rows
    // Si el paquete no se encuentra, mostrar error y redirigir
    echo "Paquete no encontrado.";
    mysqli_stmt_close($stmt); // Cerrar stmt antes de cerrar conn
    mysqli_close($conn); // Cerrar la conexión
    exit();
}

$paquete = mysqli_fetch_assoc($resultado); // <--- USANDO mysqli_fetch_assoc
mysqli_stmt_close($stmt); // <--- USANDO mysqli_stmt_close
mysqli_close($conn); // <--- Cierra la conexión a la base de datos al final del script
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

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

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
                <input type="number" name="precio" class="form-control" step="0.01" min="0" required value="<?= htmlspecialchars($paquete['precio']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select">
                    <?php
                        $tipos = ['estadía', 'pasaje', 'auto', 'completo'];
                        foreach ($tipos as $t) {
                            $selected = ($paquete['tipo'] === $t) ? 'selected' : '';
                            echo "<option value=\"" . htmlspecialchars($t) . "\" $selected>" . htmlspecialchars($t) . "</option>";
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>