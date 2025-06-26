<?php
$servidor = "sql102.infinityfree.com"; // Hostname correcto de InfinityFree
$usuario = "if0_39323873";          // Tu usuario de DB en InfinityFree
$password = "4tXaYRgkdE7";   // La contraseña que estableciste en InfinityFree (asegúrate de que sea la correcta)
$dbname = "if0_39323873_agencia";   // Tu nombre de base de datos en InfinityFree (reemplaza 'agencia' si es otro nombre)

$conn = mysqli_connect($servidor, $usuario, $password, $dbname);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}
// Opcional: configurar el conjunto de caracteres a UTF-8
mysqli_set_charset($conn, "utf8mb4");
?>