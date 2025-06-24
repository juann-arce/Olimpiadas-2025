<?php
$servidor = "localhost";
$usuario = "root";
$password = "";
$dbname = "agencia";

$conn = mysqli_connect($servidor, $usuario, $password, $dbname);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>