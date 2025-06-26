<?php
session_start();

// Incluye tu archivo de conexión a la base de datos
include 'conexion.php'; // <--- AÑADIDO ESTO

$error = ''; // Inicializa la variable de error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitiza y obtén los datos del formulario
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']); // <--- USANDO $conn
    $apellido = mysqli_real_escape_string($conn, $_POST['apellido']); // <--- USANDO $conn
    $email = mysqli_real_escape_string($conn, $_POST['email']); // <--- USANDO $conn
    $documento = mysqli_real_escape_string($conn, $_POST['documento']); // <--- USANDO $conn
    $telefono = mysqli_real_escape_string($conn, $_POST['telefono']); // <--- USANDO $conn
    $contra_plana = $_POST['contra']; // Contraseña sin hash para verificación

    // Hash de la contraseña
    $contra_hasheada = password_hash($contra_plana, PASSWORD_DEFAULT);
    $rol = "usuario"; // Rol por defecto

    // No necesitas la conexión de mysqli aquí, ya la tienes de include 'conexion.php';
    // if ($conexion->connect_error) { ... } // Esto se maneja en conexion.php

    // Validaciones (¡Añadidas y mejoradas!)
    if (empty($nombre) || empty($apellido) || empty($email) || empty($contra_plana) || empty($documento) || empty($telefono)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del correo electrónico no es válido.";
    } elseif (strlen($contra_plana) < 6) { // Validación de longitud de contraseña
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        // Verificar si el email ya existe
        $stmt_check_email = mysqli_prepare($conn, "SELECT ID_Usuario FROM usuario WHERE email = ?"); // <--- USANDO $conn
        mysqli_stmt_bind_param($stmt_check_email, "s", $email);
        mysqli_stmt_execute($stmt_check_email);
        mysqli_stmt_store_result($stmt_check_email);

        if (mysqli_stmt_num_rows($stmt_check_email) > 0) {
            $error = "El correo electrónico ya está registrado.";
        }
        mysqli_stmt_close($stmt_check_email);

        if (empty($error)) { // Solo procede si no hay errores previos
            // Preparar e insertar el nuevo usuario
            $stmt_insert = mysqli_prepare($conn, "INSERT INTO usuario (nombre, apellido, email, contra, documento, telefono, rol) VALUES (?, ?, ?, ?, ?, ?, ?)"); // <--- USANDO $conn
            mysqli_stmt_bind_param($stmt_insert, "sssssis", $nombre, $apellido, $email, $contra_hasheada, $documento, $telefono, $rol);
            // Nota: El tipo 's' para documento y telefono asumiendo que pueden contener caracteres no numéricos o guiones.
            // Si son estrictamente numéricos, considera 'i' para INT o 'd' para DOUBLE si son muy largos.

            if (mysqli_stmt_execute($stmt_insert)) { // <--- USANDO mysqli_stmt_execute
                header("Location: login.php?registro_exitoso=true"); // Redirige con un mensaje de éxito
                exit();
            } else {
                $error = "Error al registrar: " . mysqli_error($conn); // <--- USANDO $conn
            }
            mysqli_stmt_close($stmt_insert); // <--- USANDO mysqli_stmt_close
        }
    }
}

mysqli_close($conn); // <--- IMPORTANTE: Cierra la conexión al final del script
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro</title>
  <style>
    /* ESTILOS CSS */
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
    }

    .register-container {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 6px 15px rgba(0,0,0,0.1);
      width: 360px;
      max-width: 90%; /* Asegura que no se desborde en pantallas pequeñas */
      box-sizing: border-box; /* Incluye padding y border en el ancho total */
    }

    h2 {
      text-align: center;
      color: #1976d2;
      margin-bottom: 20px;
    }

    input[type="text"], input[type="email"], input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 12px;
      border-radius: 8px;
      border: 1px solid #ccc;
      box-sizing: border-box; /* Crucial para que el padding no aumente el ancho */
    }

    button {
      width: 100%;
      padding: 10px;
      background: #1976d2;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1em;
    }

    button:hover {
      background: #145ca1;
    }

    .error {
      color: red;
      text-align: center;
      margin-bottom: 10px;
      background-color: #ffe6e6;
      border: 1px solid #ffb3b3;
      padding: 8px;
      border-radius: 5px;
    }

    .link {
      text-align: center;
      margin-top: 15px; /* Aumentado para mejor separación */
      font-size: 0.95em;
    }

    .link a {
      color: #1976d2;
      text-decoration: none;
    }

    .link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="register-container">
    <h2>Registrarse</h2>
    <?php if (isset($error) && $error !== '') echo "<div class='error'>$error</div>"; ?>
    <form method="POST" action="registro.php">
      <input type="text" name="nombre" placeholder="Nombre" required>
      <input type="text" name="apellido" placeholder="Apellido" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="contra" placeholder="Contraseña" required>
      <input type="text" name="documento" placeholder="Documento" required>
      <input type="text" name="telefono" placeholder="Teléfono" required>
      <button type="submit">Registrarse</button>
    </form>
    <div class="link">
      ¿Ya tenés cuenta? <a href="login.php">Iniciar sesión</a>
    </div>
  </div>
</body>
</html>