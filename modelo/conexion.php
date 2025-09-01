
<?php
$host = "localhost";
$usuario = "root";
$contrasena = "";
$basedatos = "skillbarrio";

$conexion = new mysqli($host, $usuario, $contrasena, $basedatos);
if ($conexion->connect_error) { die("❌ Error de conexión: " . $conexion->connect_error); }

// Forzar utf8
$conexion->set_charset("utf8mb4");
?>
