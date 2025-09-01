
<?php
session_start();
include "modelo/conexion.php";
if(!isset($_SESSION['usuario_id']) || $_SESSION['es_admin']!=1){ die("❌ Sin permiso."); }
$id=intval($_GET['id']); $st=$conexion->prepare("UPDATE usuarios SET es_admin=IF(es_admin=1,0,1) WHERE id=?");
$st->bind_param("i",$id); $st->execute(); $st->close(); header("Location: usuarios_admin.php"); exit();
