<?php
session_start();
include "modelo/conexion.php";

$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];
$categoria = $_POST['categoria'];
$usuario_id = $_SESSION['usuario_id'];

// Guardar el servicio
$conexion->query("INSERT INTO servicios (titulo, descripcion, categoria, usuario_id) VALUES ('$titulo', '$descripcion', '$categoria', $usuario_id)");
$servicio_id = $conexion->insert_id;

// Guardar imágenes
if(isset($_FILES['imagenes'])){
    $total = count($_FILES['imagenes']['name']);
    for($i=0; $i<$total; $i++){
        $tmpFile = $_FILES['imagenes']['tmp_name'][$i];
        $nombre = time().'_'.$_FILES['imagenes']['name'][$i];
        $ruta = 'uploads/'.$nombre;
        move_uploaded_file($tmpFile, $ruta);

        // Insertar ruta en la tabla servicio_imagenes
        $conexion->query("INSERT INTO servicio_imagenes (servicio_id, imagen) VALUES ($servicio_id, '$ruta')");
    }
}

header("Location: detalle_servicio.php?id=$servicio_id");
?>
