
<?php
session_start();
include "modelo/conexion.php";
include "config_global.php";
if(!isset($_SESSION['usuario_id']) || $_SESSION['es_admin']!=1){ die("❌ No tienes permiso."); }
$res=$conexion->query("SELECT id, nombre, correo, es_admin, activo FROM usuarios ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Usuarios - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
  <?php include "menu.php"; ?>
  <div class="container mt-3">
    <h2>👤 Administración de Usuarios</h2>
    <table class="table table-bordered table-striped mt-3">
      <thead class="table-dark"><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody>
        <?php while($u=$res->fetch_assoc()): ?>
          <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['nombre']) ?></td>
            <td><?= htmlspecialchars($u['correo']) ?></td>
            <td><?= $u['es_admin']?'Administrador':'Usuario' ?></td>
            <td><?= $u['activo']?'Activo ✅':'Inactivo ❌' ?></td>
            <td>
              <a class="btn btn-sm btn-primary" href="editar_usuario_admin.php?id=<?= $u['id'] ?>">Editar</a>
              <a class="btn btn-sm btn-warning" href="toggle_admin.php?id=<?= $u['id'] ?>"><?= $u['es_admin']?'Quitar Admin':'Hacer Admin' ?></a>
              <a class="btn btn-sm btn-secondary" href="toggle_estado.php?id=<?= $u['id'] ?>"><?= $u['activo']?'Desactivar':'Activar' ?></a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
