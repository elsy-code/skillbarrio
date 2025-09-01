
<?php
session_start();
include "modelo/conexion.php";
if(!isset($_SESSION['usuario_id']) || $_SESSION['es_admin']!=1){ die("❌ Sin permiso."); }
$id = isset($_GET['id'])?intval($_GET['id']):0;
$stmt=$conexion->prepare("SELECT * FROM usuarios WHERE id=?"); $stmt->bind_param("i",$id); $stmt->execute(); $res=$stmt->get_result(); $usuario=$res->fetch_assoc(); $stmt->close();
if(!$usuario){ die("No encontrado."); }
$mensaje="";
if($_SERVER["REQUEST_METHOD"]=="POST"){
  $nombre=$_POST['nombre']; $correo=$_POST['correo'];
  $st=$conexion->prepare("UPDATE usuarios SET nombre=?, correo=? WHERE id=?");
  $st->bind_param("ssi",$nombre,$correo,$id);
  if($st->execute()){ $mensaje="✅ Usuario actualizado."; $usuario['nombre']=$nombre; $usuario['correo']=$correo; } else { $mensaje="❌ Error: ".$conexion->error; }
  $st->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><title>Editar Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
  <?php include "menu.php"; ?>
  <div class="container mt-3">
    <h2>Editar Usuario</h2>
    <?php if($mensaje): ?><div class="alert alert-info"><?= $mensaje ?></div><?php endif; ?>
    <form method="POST" class="card p-4 shadow" style="max-width:500px;">
      <div class="mb-3"><label class="form-label">Nombre</label><input name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre']) ?>" required></div>
      <div class="mb-3"><label class="form-label">Correo</label><input name="correo" type="email" class="form-control" value="<?= htmlspecialchars($usuario['correo']) ?>" required></div>
      <button class="btn btn-primary w-100">Guardar</button>
      <a href="usuarios_admin.php" class="btn btn-secondary w-100 mt-2">Volver</a>
    </form>
  </div>
</body>
</html>
