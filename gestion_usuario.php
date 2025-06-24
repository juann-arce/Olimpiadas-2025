<?php
session_start();

// Verificar si el usuario está logueado Y si su rol es 'admin'
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    // Si no es admin o no está logueado, redirigir al index o a login
    header("Location: index.php"); 
    exit();
}

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "agencia");
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Obtener todos los usuarios de la base de datos
$sql = "SELECT ID_Usuario, nombre, apellido, email, documento, telefono, rol FROM usuario ORDER BY ID_Usuario ASC"; //
$resultado = $conexion->query($sql);

$usuarios = [];
if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $usuarios[] = $fila;
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f3f6f9; }
        .table-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Agencia de Viajes</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="gestion_pedidos.php">Gestión Pedidos</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Cerrar Sesión (Admin)</a></li>
                    <?php elseif (isset($_SESSION['usuario_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="carrito.php">Carrito</a></li>
                        <li class="nav-item"><a class="nav-link" href="pedidos.php">Mis Pedidos</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Iniciar Sesión</a></li>
                        <li class="nav-item"><a class="nav-link" href="registro.php">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4">Gestión de Usuarios</h2>

        <?php if (empty($usuarios)): ?>
            <div class="alert alert-info">No hay usuarios registrados.</div>
        <?php else: ?>
            <div class="table-responsive table-container">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Email</th>
                            <th>Documento</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['ID_Usuario']) ?></td>
                                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                <td><?= htmlspecialchars($usuario['apellido']) ?></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td><?= htmlspecialchars($usuario['documento']) ?></td>
                                <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                                <td>
                                    <?php
                                        // Diferenciar el rol con badges de Bootstrap
                                        $rol = htmlspecialchars($usuario['rol']);
                                        if ($rol === 'admin') {
                                            echo '<span class="badge bg-danger">Admin</span>';
                                        } else {
                                            echo '<span class="badge bg-info text-dark">Usuario</span>';
                                        }
                                    ?>
                                </td>
                                </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <footer class="text-center mt-5 p-4 bg-light">
        <small>&copy; 2025 Agencia de Viajes. Todos los derechos reservados.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>