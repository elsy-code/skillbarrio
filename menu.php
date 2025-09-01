<?php
require_once "helpers.php";
?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><?= e($config['nombre_sitio'] ?? 'SkillBarrio') ?></a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="servicios.php">Servicios</a></li>
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <?php if (empty($_SESSION['es_admin']) || $_SESSION['es_admin'] == 0): ?>
                <li class="nav-item"><a class="nav-link" href="publicar.php">Publicar</a></li>
                <li class="nav-item"><a class="nav-link" href="mis_servicios.php">Mis servicios</a></li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="admin_panel.php">Admin</a></li>
                <li class="nav-item"><a class="nav-link" href="usuarios_admin.php">Usuarios</a></li>
            <?php endif; ?>
            <li class="nav-item"><a class="nav-link" href="logout.php">Salir</a></li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="login.php">Entrar</a></li>
            <li class="nav-item"><a class="nav-link" href="registro.php">Registrarse</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
