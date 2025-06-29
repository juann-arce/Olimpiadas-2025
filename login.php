<?php
session_start();

// Si el usuario ya está logueado, redirige a la página principal según su rol
if (isset($_SESSION['usuario_id'])) {
    if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {
        header("Location: gestion_pedidos.php"); // Redirige a administradores al panel
    } else {
        header("Location: index.php"); // Redirige a usuarios normales al index de paquetes
    }
    exit();
}

// Incluye tu archivo de conexión a la base de datos
include 'conexion.php'; // <--- AÑADIDO ESTO

$error = ''; // Inicializa la variable de error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $contra = $_POST['contra'];

    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    // No necesitas la conexión de mysqli aquí, ya la tienes de include 'conexion.php';
    // if ($conexion->connect_error) { ... } // Esto se maneja en conexion.php

    // Preparar la consulta usando $conn (la variable de tu conexion.php)
    $stmt = mysqli_prepare($conn, "SELECT ID_Usuario, nombre, rol, contra FROM usuario WHERE email = ?"); // <--- USANDO $conn
    mysqli_stmt_bind_param($stmt, "s", $email); // <--- USANDO mysqli_stmt_bind_param
    mysqli_stmt_execute($stmt); // <--- USANDO mysqli_stmt_execute
    mysqli_stmt_store_result($stmt); // <--- USANDO mysqli_stmt_store_result

    if (mysqli_stmt_num_rows($stmt) === 1) { // <--- USANDO mysqli_stmt_num_rows
        mysqli_stmt_bind_result($stmt, $id_usuario, $nombre_usuario, $rol_usuario, $hash_contrasena); // <--- USANDO mysqli_stmt_bind_result
        mysqli_stmt_fetch($stmt); // <--- USANDO mysqli_stmt_fetch

        if (password_verify($contra, $hash_contrasena)) {
            $_SESSION['usuario_id'] = $id_usuario;
            $_SESSION['usuario_nombre'] = $nombre_usuario;
            $_SESSION['usuario_rol'] = $rol_usuario; // Guardamos el rol del usuario

            if ($_SESSION['usuario_rol'] === 'admin') {
                header("Location: gestion_pedidos.php"); // Redirige a los administradores
            } else {
                header("Location: index.php"); // Redirige a los usuarios normales
            }
            exit(); // ¡Importante!
        } else {
            $error = "Email o contraseña incorrectos.";
        }
    } else {
        $error = "Email o contraseña incorrectos.";
    }

    mysqli_stmt_close($stmt); // <--- USANDO mysqli_stmt_close
}

mysqli_close($conn); // <--- IMPORTANTE: Cierra la conexión al final del script
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Agencia de Viajes</title>
    <style>
        /* ESTILOS CSS ESPECÍFICOS PARA EL FORMULARIO DE LOGIN */
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            width: 320px;
            max-width: 90%;
            box-sizing: border-box;
        }

        h2 {
            text-align: center;
            color: #1976d2;
            margin-bottom: 20px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-sizing: border-box;
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
        }

        .link {
            text-align: center;
            margin-top: 10px;
            font-size: 0.9em;
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
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <?php if (isset($error) && $error !== '') echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="contra" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>
        <div class="link">
            ¿No tenés cuenta? <a href="registro.php">Registrate</a>
        </div>
    </div>
</body>
</html>