<?php
session_start();
include "config_global.php";
include "modelo/conexion.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_servicio = intval($_GET['id']);

// Obtener servicio
$sql = "SELECT s.titulo, s.descripcion, s.categoria, s.telefono, s.ubicacion, u.nombre
        FROM servicios s
        JOIN usuarios u ON s.usuario_id = u.id
        WHERE s.id = $id_servicio
        LIMIT 1";
$resultado = $conexion->query($sql);

if (!$resultado || $resultado->num_rows == 0) {
    echo "<div class='alert alert-danger text-center mt-5'>Servicio no encontrado.</div>";
    exit;
}

$servicio = $resultado->fetch_assoc();

// Obtener imágenes
$imagenes = $conexion->query("SELECT imagen FROM servicio_imagenes WHERE servicio_id=$id_servicio");

$imagen_principal = null;
if($imagenes->num_rows == 0){
    $sql_img = "SELECT imagen FROM servicios WHERE id=$id_servicio AND imagen IS NOT NULL";
    $res_img = $conexion->query($sql_img);
    if($res_img && $res_img->num_rows > 0){
        $row = $res_img->fetch_assoc();
        $imagen_principal = $row['imagen'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($servicio['titulo']) ?> - Detalle</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f7f7f7; font-family: 'Segoe UI', sans-serif; }
.detalle-container { max-width: 900px; margin: 50px auto; background: #fff; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); padding: 25px; }
.detalle-row { display: flex; flex-direction: column; gap: 25px; }
.detalle-img { border-radius: 12px; width: 100%; height: 300px; object-fit: contain; background-color: #f0f0f0; transition: transform 0.3s ease; }
.detalle-img:hover { transform: scale(1.05); }
.thumbnail-container { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
.thumbnail-container img { width: 60px; height: 60px; object-fit: contain; border-radius: 5px; cursor: pointer; border: 2px solid transparent; transition: border 0.3s ease; }
.thumbnail-container img:hover { border-color: #0078ff; }
.thumbnail-container img.active-thumb { border-color: #0078ff; }
.detalle-info h2 { font-weight: 700; margin-bottom: 15px; font-size: 1.8rem; color: #333; }
.detalle-info p { font-size: 1rem; color: #555; margin-bottom: 10px; line-height: 1.5; }
.detalle-info .badge { font-size: 0.85rem; padding: 5px 10px; }
.btn-contact { margin-top: 12px; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.btn-contact:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.divider { border-top: 1px solid #eee; margin: 20px 0; }
@media (min-width: 768px) {
  .detalle-row { flex-direction: row; }
  .detalle-info { width: 50%; }
  .carousel-container { width: 50%; }
}
</style>
</head>
<body>
<?php include "menu.php"; ?>

<div class="container detalle-container">
  <div class="detalle-row">

    <!-- Slider con miniaturas -->
    <div class="carousel-container">
      <?php 
      $todas_imagenes = [];
      if($imagenes->num_rows > 0){
          while($img = $imagenes->fetch_assoc()){
              $todas_imagenes[] = $img['imagen'];
          }
      } elseif($imagen_principal) {
          $todas_imagenes[] = $imagen_principal;
      } else {
          $todas_imagenes[] = 'public/img/no-image.png';
      }
      ?>
      <img id="imagen-principal" src="<?= htmlspecialchars($todas_imagenes[0]) ?>" class="detalle-img">

      <?php if(count($todas_imagenes) > 1): ?>
      <div class="thumbnail-container">
        <?php foreach($todas_imagenes as $index => $img_thumb): ?>
          <img src="<?= htmlspecialchars($img_thumb) ?>" class="<?= $index===0?'active-thumb':'' ?>" onclick="cambiarImagen('<?= htmlspecialchars($img_thumb) ?>', this)">
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Información del servicio -->
    <div class="detalle-info">
      <h2><?= htmlspecialchars($servicio['titulo']) ?></h2>
      <p><?= htmlspecialchars($servicio['descripcion']) ?></p>
      <p><span class="badge bg-primary"><?= htmlspecialchars($servicio['categoria']) ?></span></p>
      <div class="divider"></div>
      <p><strong>Publicado por:</strong> <?= htmlspecialchars($servicio['nombre']) ?></p>
      <p><strong>Teléfono:</strong> <a href="tel:<?= htmlspecialchars($servicio['telefono']) ?>"><?= htmlspecialchars($servicio['telefono']) ?></a></p>
      <p><strong>Ubicación:</strong> <?= htmlspecialchars($servicio['ubicacion']) ?></p>

      <a href="tel:<?= htmlspecialchars($servicio['telefono']) ?>" class="btn btn-success btn-contact me-2">Llamar</a>
      <a href="https://wa.me/<?= preg_replace('/\D/', '', $servicio['telefono']) ?>" target="_blank" class="btn btn-success btn-contact">WhatsApp</a>
      <br>
      <a href="index.php" class="btn btn-secondary btn-contact mt-3">Volver al inicio</a>
    </div>
  </div>
</div>

<script>
function cambiarImagen(src, thumb){
    document.getElementById('imagen-principal').src = src;
    document.querySelectorAll('.thumbnail-container img').forEach(img => img.classList.remove('active-thumb'));
    thumb.classList.add('active-thumb');
}
</script>

</body>
</html>
