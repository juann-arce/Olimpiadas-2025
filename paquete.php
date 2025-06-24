<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$conexion = new mysqli("localhost", "root", "", "agencia");
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$usuario_id = $_SESSION['usuario_id'];

$sql = "
SELECT 
  p.ID_Pedido, 
  p.Fecha, 
  p.Estado, 
  GROUP_CONCAT(pa.nombre SEPARATOR ', ') AS paquetes
FROM pedidos p
JOIN pedido_detalles d ON p.ID_Pedido = d.ID_Pedido
JOIN paquetes pa ON d.ID_Reserva = pa.ID_Reserva
WHERE p.ID_Usuario = ?
GROUP BY p.ID_Pedido
ORDER BY p.Fecha DESC
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Pedidos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
  <h2>Mis Pedidos</h2>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Paquetes</th>
        <th>Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($fila = $resultado->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($fila['Fecha']) ?></td>
          <td><?= htmlspecialchars($fila['paquetes']) ?></td>
          <td>
            <?php
              if ($fila['Estado'] === 'confirmado') {
                echo '<span class="badge bg-warning text-dark">Confirmado</span>';
              } elseif ($fila['Estado'] === 'pagado') {
                echo '<span class="badge bg-success">Pagado</span>';
              } elseif ($fila['Estado'] === 'cancelado') {
                echo '<span class="badge bg-danger">Cancelado</span>';
              }
            ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>
</html>
