<?php
require_once "modelo/conexion.php";
require_once "helpers.php";
require_once "config_global.php";
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['es_admin'] != 1) { die("❌ No tienes permiso."); }

ensure_csrf_token();
$mensaje = "";

if (isset($_GET['eliminar'])) {
    $token = $_GET['token'] ?? '';
    if (!check_csrf_token($token)) {
        $mensaje = "❌ Token inválido.";
    } else {
        $id = intval($_GET['eliminar']);
        $stmt = $conexion->prepare("DELETE FROM servicios WHERE id = ?");
        if ($stmt) { $stmt->bind_param("i", $id); if ($stmt->execute()) $mensaje = "✅ Servicio eliminado."; else $mensaje = "❌ Error al eliminar."; $stmt->close(); }
        else $mensaje = "❌ Error en la consulta: " . $conexion->error;
    }
}

$sql = "SELECT s.id, s.titulo, s.descripcion, s.categoria, s.telefono, s.ubicacion, s.imagen, u.nombre AS usuario, u.correo
        FROM servicios s JOIN usuarios u ON s.usuario_id = u.id ORDER BY s.id DESC";
$res = $conexion->query($sql);
$servicios = [];
if ($res) while ($r = $res->fetch_assoc()) $servicios[] = $r;
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="public/css/styles.css"></head>
<body>
<?php include "menu.php"; ?>
<div class="container mt-3">
  <h2 class="text-center">Panel de Administración</h2>
  <?php if($mensaje): ?><div class="alert alert-info text-center"><?= e($mensaje) ?></div><?php endif; ?>
  <table class="table table-bordered table-striped mt-3">
    <thead class="table-dark"><tr><th>Imagen</th><th>Título</th><th>Categoría</th><th>Descripción</th><th>Ubicación</th><th>Teléfono</th><th>Usuario</th><th>Correo</th><th>Acciones</th></tr></thead>
    <tbody>
      <?php if(count($servicios)>0): foreach($servicios as $s): ?>
        <tr>
          <td><?php if(!empty($s['imagen'])): ?><img src="<?= e($s['imagen']) ?>" width="80"><?php else: ?>Sin imagen<?php endif; ?></td>
          <td><?= e($s['titulo']) ?></td>
          <td><?= e($s['categoria']) ?></td>
          <td><?= e($s['descripcion']) ?></td>
          <td><?= e($s['ubicacion']) ?></td>
          <td><?= e($s['telefono']) ?></td>
          <td><?= e($s['usuario']) ?></td>
          <td><?= e($s['correo']) ?></td>
          <td>
            <a class="btn btn-warning btn-sm" href="editar_servicio_admin.php?id=<?= $s['id'] ?>">Editar</a>
            <a class="btn btn-danger btn-sm" href="admin_panel.php?eliminar=<?= $s['id'] ?>&token=<?= e($_SESSION['csrf_token']) ?>" onclick="return confirm('¿Eliminar?')">Eliminar</a>
          </td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="9" class="text-center">No hay servicios.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
