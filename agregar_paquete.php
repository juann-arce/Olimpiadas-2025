<?php
session_start();

// Opcional: Proteger esta página para que solo admins puedan acceder
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: index.php'); // Redirige si no es admin
    exit;
}

// Incluye tu archivo de conexión a la base de datos
include 'conexion.php'; // <--- AÑADIDO ESTO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitiza y obtén los datos del formulario
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre'] ?? ''); // <--- USANDO $conn
    $descripcion = mysqli_real_escape_string($conn, $_POST['descripcion'] ?? ''); // <--- USANDO $conn
    $precio = floatval($_POST['precio'] ?? 0); // Convertir a float
    $tipo = mysqli_real_escape_string($conn, $_POST['tipo'] ?? ''); // <--- USANDO $conn
    $destino = mysqli_real_escape_string($conn, $_POST['destino'] ?? ''); // <--- USANDO $conn

    // Validar datos básicos
    if (empty($nombre) || empty($descripcion) || $precio <= 0 || empty($tipo) || empty($destino) || !isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        $error_message = "Por favor, complete todos los campos y suba una imagen válida.";
    } else {
        // Procesar imagen
        $imagenNombre = $_FILES['imagen']['name'];
        $imagenTmp = $_FILES['imagen']['tmp_name'];
        $directorioDestino = 'imagenes/';
        $rutaDestino = $directorioDestino . basename($imagenNombre);

        // Crear carpeta si no existe
        if (!file_exists($directorioDestino)) {
            mkdir($directorioDestino, 0777, true);
        }

        if (move_uploaded_file($imagenTmp, $rutaDestino)) {
            // No necesitas la conexión de mysqli aquí, ya la tienes de include 'conexion.php';
            // if ($conexion->connect_error) { ... } // Esto se maneja en conexion.php

            // Preparar la consulta para insertar el paquete
            $stmt = mysqli_prepare($conn, "INSERT INTO paquetes (nombre, descripcion, precio, tipo, destino, imagen) VALUES (?, ?, ?, ?, ?, ?)"); // <--- USANDO $conn
            // Tipos de datos para bind_param: s (string), i (integer), d (double)
            mysqli_stmt_bind_param($stmt, "ssisss", $nombre, $descripcion, $precio, $tipo, $destino, $rutaDestino); // <--- USANDO mysqli_stmt_bind_param

            if (mysqli_stmt_execute($stmt)) { // <--- USANDO mysqli_stmt_execute
                // Redirigir a la página principal con mensaje de éxito
                header("Location: index.php?mensaje=paquete_agregado_exito");
                exit();
            } else {
                // Manejar error en la base de datos
                $error_message = "Error al guardar en la base de datos: " . mysqli_error($conn); // <--- USANDO $conn
                // Si hubo un error en DB, eliminar la imagen subida para limpiar
                if (file_exists($rutaDestino)) {
                    unlink($rutaDestino);
                }
            }

            mysqli_stmt_close($stmt); // <--- USANDO mysqli_stmt_close
        } else {
            $error_message = "Error al subir la imagen. Verifique los permisos de la carpeta 'imagenes'.";
        }
    }
}

mysqli_close($conn); // <--- IMPORTANTE: Cierra la conexión al final del script
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

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" action="agregar_paquete.php">
            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" required><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Precio</label>
                <input type="number" name="precio" class="form-control" step="0.01" min="0" required value="<?= htmlspecialchars($_POST['precio'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select" required>
                    <option value="">Seleccione un tipo</option>
                    <option value="estadía" <?= (($_POST['tipo'] ?? '') == 'estadía') ? 'selected' : '' ?>>Estadía</option>
                    <option value="pasaje" <?= (($_POST['tipo'] ?? '') == 'pasaje') ? 'selected' : '' ?>>Pasaje</option>
                    <option value="auto" <?= (($_POST['tipo'] ?? '') == 'auto') ? 'selected' : '' ?>>Auto</option>
                    <option value="completo" <?= (($_POST['tipo'] ?? '') == 'completo') ? 'selected' : '' ?>>Completo</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Destino</label>
                <input type="text" name="destino" class="form-control" required value="<?= htmlspecialchars($_POST['destino'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Imagen (Archivo)</label>
                <input type="file" name="imagen" class="form-control" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary">Agregar</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>