<?php
require_once "modelo/conexion.php";
require_once "helpers.php";
require_once "config_global.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$categoria = $_GET['categoria'] ?? "";

$sql_categorias = "SELECT DISTINCT categoria FROM servicios ORDER BY categoria ASC";
$result_categorias = $conexion->query($sql_categorias);

if (!empty($categoria)) {
    $sql = "SELECT s.id, s.titulo, s.descripcion, s.categoria, s.telefono, s.ubicacion, 
                   s.latitud, s.longitud, s.imagen, u.nombre AS usuario
            FROM servicios s
            JOIN usuarios u ON s.usuario_id = u.id
            WHERE s.categoria = ?
            ORDER BY s.id DESC";
    $stmt = $conexion->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $categoria);
        $stmt->execute();
        $resultado = $stmt->get_result();
    } else {
        die("❌ Error SQL: " . $conexion->error);
    }
} else {
    $sql = "SELECT s.id, s.titulo, s.descripcion, s.categoria, s.telefono, s.ubicacion, 
                   s.latitud, s.longitud, s.imagen, u.nombre AS usuario
            FROM servicios s
            JOIN usuarios u ON s.usuario_id = u.id
            ORDER BY s.id DESC";
    $resultado = $conexion->query($sql);
}

$servicios = [];
if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $servicios[] = $fila;
    }
}

$rol = $_SESSION['rol'] ?? "usuario"; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($config['nombre_sitio']) ?> - Servicios</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  .card-custom {
    border: 1px solid #eee;
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    background: #fff;
  }
  .card-custom:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
  }
  .card-custom img {
    width: 100%;
    height: 220px;
    object-fit: contain; 
    background: #f9f9f9;
    padding: 10px;
  }
  .card-body-custom {
    padding: 12px;
  }
  .card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
  }
  .card-text {
    font-size: 0.9rem;
    color: #555;
  }
  .card-info {
    font-size: 0.8rem;
    color: #777;
    margin-bottom: 5px;
  }
</style>
</head>
<body class="bg-light">
<?php include "menu.php"; ?>
<div class="container mt-4">
  <h2 class="text-center">📌 Servicios Disponibles</h2>

  <form method="GET" action="servicios.php" class="mb-3 text-center">
    <label for="categoria" class="form-label">Filtrar por categoría:</label>
    <select name="categoria" id="categoria" class="form-select d-inline-block w-auto">
      <option value="">Todas</option>
      <?php while ($cat = $result_categorias->fetch_assoc()): ?>
        <option value="<?= e($cat['categoria']) ?>" <?= ($categoria == $cat['categoria']) ? 'selected' : '' ?>>
          <?= e($cat['categoria']) ?>
        </option>
      <?php endwhile; ?>
    </select>
    <button type="submit" class="btn btn-primary ms-2">Filtrar</button>
    <a href="servicios.php" class="btn btn-secondary ms-2">Ver todos</a>
  </form>

  <?php if ($rol === "admin"): ?>
    <table class="table table-bordered table-striped mt-3">
      <thead class="table-dark">
        <tr>
          <th>Imagen</th>
          <th>Título</th>
          <th>Categoría</th>
          <th>Descripción</th>
          <th>Ubicación</th>
          <th>Teléfono</th>
          <th>Publicado por</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($servicios) > 0): ?>
          <?php foreach ($servicios as $s): ?>
            <tr>
              <td>
                <?php if (!empty($s['imagen'])): ?>
                  <img src="<?= e($s['imagen']) ?>" class="img-thumbnail" width="100" alt="Imagen servicio">
                <?php else: ?>
                  <span class="text-muted">Sin imagen</span>
                <?php endif; ?>
              </td>
              <td><?= e($s['titulo']) ?></td>
              <td><?= e($s['categoria']) ?></td>
              <td><?= e($s['descripcion']) ?></td>
              <td><?= e($s['ubicacion']) ?></td>
              <td><?= e($s['telefono']) ?></td>
              <td><?= e($s['usuario']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center">❌ No hay servicios en esta categoría.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

  <?php else: ?>
    <div class="row g-3 mt-3">
      <?php if (count($servicios) > 0): ?>
        <?php foreach ($servicios as $s): ?>
          <div class="col-6 col-md-4 col-lg-3">
            <div class="card-custom">
              <?php if (!empty($s['imagen'])): ?>
                <img src="<?= e($s['imagen']) ?>" alt="Imagen servicio">
              <?php else: ?>
                <img src="https://via.placeholder.com/300x200?text=Sin+Imagen" alt="Sin imagen">
              <?php endif; ?>
              <div class="card-body-custom">
                <h5 class="card-title"><?= e($s['titulo']) ?></h5>
                <p class="card-text"><?= substr(e($s['descripcion']), 0, 50) ?>...</p>
                <p class="card-info"><i class="fas fa-map-marker-alt"></i> <?= e($s['ubicacion']) ?></p>
                <p class="card-info"><i class="fas fa-user"></i> <?= e($s['usuario']) ?></p>
                <a href="detalle_servicios.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary w-100 mt-2">Ver más</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-center">❌ No hay servicios en esta categoría.</p>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
<script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
</body>
</html>
