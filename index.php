<?php
session_start(); // Inicia la sesión para acceder a las variables de sesión

// Determina si el usuario actual es un administrador
$es_admin = (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin');
$usuario_logueado = isset($_SESSION['usuario_id']); // Verifica si hay un usuario logueado
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta de Paquetes Turísticos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f3f6f9; }
        .paquete-card {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .paquete-card:hover {
            transform: scale(1.03);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
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
                    <?php if ($es_admin): ?>
                        <li class="nav-item"><a class="nav-link" href="gestion_pedidos.php">Gestión Pedidos</a></li>
                        <li class="nav-item"><a class="nav-link" href="gestion_usuarios.php">Gestión Usuarios</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Cerrar Sesión (Admin)</a></li>
                    <?php elseif ($usuario_logueado): ?>
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

    <div class="container mt-4">
        <h1 class="mb-4">Paquetes Turísticos Disponibles</h1>
        <?php
        // Mensajes de éxito/error (ej. después de agregar/editar/eliminar paquete)
        if (isset($_GET['mensaje'])) {
            $mensaje = htmlspecialchars($_GET['mensaje']);
            $clase_alerta = 'alert-success';
            if (strpos($mensaje, 'error') !== false) {
                $clase_alerta = 'alert-danger';
            }
            echo '<div class="alert ' . $clase_alerta . ' alert-dismissible fade show" role="alert">';
            echo ($mensaje === 'paquete_actualizado_exito') ? 'Paquete actualizado exitosamente.' : '';
            echo ($mensaje === 'paquete_agregado_exito') ? 'Paquete agregado exitosamente.' : '';
            echo ($mensaje === 'paquete_eliminado_exito') ? 'Paquete eliminado exitosamente.' : '';
            echo ($mensaje === 'error') ? 'Ha ocurrido un error.' : ''; // Mensaje genérico para otros errores
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
        }
        ?>

        <?php if ($es_admin): ?>
            <div class="mb-3">
                <a href="agregar_paquete.php" class="btn btn-success">Crear Nuevo Paquete</a>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php
                include 'conexion.php'; // <--- AÑADIDO ESTO (Ahora $conn está disponible)

                $sql = "SELECT * FROM paquetes";
                $resultado = mysqli_query($conn, $sql); // <--- USANDO mysqli_query($conn, ...)

                if (mysqli_num_rows($resultado) > 0) { // <--- USANDO mysqli_num_rows
                    while ($fila = mysqli_fetch_assoc($resultado)) { // <--- USANDO mysqli_fetch_assoc
                        echo '<div class="col-md-4">';
                        echo '    <div class="card paquete-card mb-4">';
                        
                        if (!empty($fila['imagen']) && file_exists($fila['imagen'])) {
                            echo '        <img src="' . htmlspecialchars($fila['imagen']) . '" class="card-img-top" alt="Imagen del paquete">';
                        } else {
                            // Imagen por defecto si no hay imagen o no existe
                            echo '        <img src="imagenes/default.jpg" class="card-img-top" alt="Imagen no disponible">'; 
                        }

                        echo '        <div class="card-body">';
                        echo '            <h5 class="card-title">' . htmlspecialchars($fila['nombre']) . '</h5>';
                        echo '            <p class="card-text">' . htmlspecialchars($fila['descripcion']) . '</p>';
                        echo '            <p class="card-text"><strong>Destino:</strong> ' . htmlspecialchars($fila['destino']) . '</p>';
                        echo '            <p class="card-text"><strong>Tipo:</strong> ' . htmlspecialchars($fila['tipo']) . '</p>';
                        echo '            <p class="card-text"><strong>Precio:</strong> $' . number_format($fila['precio'], 2, ',', '.') . '</p>'; // Formato de moneda
                        
                        // Formulario para agregar al carrito (SOLO VISIBLE para usuarios logueados NO ADMINS)
                        if ($usuario_logueado && !$es_admin) {
                            echo '            <form method="POST" action="agregar_al_carrito.php">';
                            echo '                <input type="hidden" name="id_reserva" value="' . htmlspecialchars($fila['ID_Reserva']) . '">';
                            echo '                <label for="cantidad_' . htmlspecialchars($fila['ID_Reserva']) . '">Cantidad:</label>';
                            echo '                <input type="number" id="cantidad_' . htmlspecialchars($fila['ID_Reserva']) . '" name="cantidad" value="1" min="1" class="form-control mb-2">';
                            echo '                <button type="submit" class="btn btn-success w-100">Agregar al carrito</button>';
                            echo '            </form>';
                        } elseif (!$usuario_logueado) { // Si no está logueado, sigue viendo el mensaje para iniciar sesión
                            echo '<p class="text-center text-muted mt-3"><a href="login.php">Inicia sesión</a> para agregar al carrito</p>';
                        }

                        // Botones de Editar y Eliminar (Solo para administradores)
                        if ($es_admin) {
                            echo '            <div class="d-flex justify-content-between mt-2">';
                            echo '                <a href="editar_paquete.php?id=' . htmlspecialchars($fila['ID_Reserva']) . '" class="btn btn-warning btn-sm">Editar</a>';
                            echo '                <a href="eliminar_paquete.php?id=' . htmlspecialchars($fila['ID_Reserva']) . '" class="btn btn-danger btn-sm" onclick="return confirm(\'¿Seguro que desea eliminar este paquete?\')">Eliminar</a>';
                            echo '            </div>';
                        }

                        echo '        </div>';
                        echo '    </div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="col-12"><div class="alert alert-info" role="alert">No hay paquetes turísticos disponibles en este momento.</div></div>';
                }

                mysqli_close($conn); // <--- Cierra la conexión a la base de datos al final del script
            ?>
        </div>
    </div>

    <footer class="text-center mt-5 p-4 bg-light">
        <small>&copy; 2025 Agencia de Viajes. Todos los derechos reservados.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>