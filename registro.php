<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $contra = password_hash($_POST['contra'], PASSWORD_DEFAULT);
    $documento = $_POST['documento'];
    $telefono = $_POST['telefono'];
    $rol = "usuario";

    $conexion = new mysqli("localhost", "root", "", "agencia");

    if ($conexion->connect_error) {
        die("Conexión fallida: " . $conexion->connect_error);
    }

    $stmt = $conexion->prepare("INSERT INTO usuario (nombre, apellido, email, contra, documento, telefono, rol) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $nombre, $apellido, $email, $contra, $documento, $telefono, $rol);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Error al registrar: " . $stmt->error;
    }

    $stmt->close();
    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .register-container {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 6px 15px rgba(0,0,0,0.1);
      width: 360px;
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
    }

    button {
      width: 100%;
      padding: 10px;
      background: #1976d2;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }

    button:hover {
      background: #145ca1;
    }

    .error {
      color: red;
      text-align: center;
      margin-bottom: 10px;
    }

    .link {
      text-align: center;
      margin-top: 10px;
    }

    .link a {
      color: #1976d2;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="register-container">
    <h2>Registrarse</h2>
    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    <form method="POST">
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