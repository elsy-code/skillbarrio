<?php
session_start();
include "config_global.php";
include "modelo/conexion.php";

if (isset($_SESSION['usuario_id']) && isset($_SESSION['rol'])) {
  if ($_SESSION['rol'] === 'admin') {
    header("Location: admin_panel.php"); 
    exit();
  } elseif ($_SESSION['rol'] === 'usuario') {
    header("Location: mis_servicios.php"); 
    exit();
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($config['nombre_sitio']) ?> - Inicio</title>

  
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
    }

    .navbar {
      background: linear-gradient(90deg, #007bff, #6610f2);
    }

    .navbar-brand {
      font-weight: 600;
      color: #fff !important;
    }

    .nav-link {
      color: #fff !important;
      font-weight: 500;
    }

    .hero {
      background: url('https://images.unsplash.com/photo-1522199710521-72d69614c702?auto=format&fit=crop&w=1200&q=80') no-repeat center center/cover;
      height: 60vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      text-align: center;
      border-radius: 15px;
      margin-bottom: 40px;
    }

    .hero h1 {
      font-size: 3rem;
      font-weight: 700;
      text-shadow: 0px 3px 8px rgba(0, 0, 0, 0.6);
    }

    .card {
      border: none;
      border-radius: 15px;
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .card img {
      height: 200px;
      object-fit: cover;
    }

    footer {
      margin-top: 50px;
      background: #343a40;
      color: #fff;
      padding: 20px;
      text-align: center;
      border-radius: 15px 15px 0 0;
    }
  </style>
</head>

<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="index.php"><?= htmlspecialchars($config['nombre_sitio']) ?></a>
      <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
          <li class="nav-item"><a class="nav-link" href="publicar.php">Publicar</a></li>
          <li class="nav-item"><a class="nav-link" href="mis_servicios.php">Mis Servicios</a></li>

          <?php if (isset($_SESSION['usuario_id'])): ?>
            <li class="nav-item">
              <a class="nav-link btn btn-danger text-white px-3 ms-2" href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
              </a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link btn btn-success text-white px-3 ms-2" href="login.php">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>
  <!-- Hero Banner -->
  <div class="container">
    <div class="hero">
      <div>
        <h1>Encuentra el servicio que necesitas</h1>
        <p class="lead">Publica, busca y conecta con los mejores proveedores</p>
        <a href="publicar.php" class="btn btn-lg btn-warning mt-3"><i class="fas fa-plus-circle"></i> Publica tu servicio</a>
      </div>
    </div>
  </div>

  <!-- Servicios -->
  <div class="container">
    <h2 class="mb-4 text-center fw-bold">Servicios Destacados</h2>
    <div class="row g-4">

      <?php
      $result = $conexion->query("SELECT * FROM servicio_imagenes  ORDER BY id DESC LIMIT 6");
      while ($row = $result->fetch_assoc()) {
      ?>
        <div class="col-md-4">
          <div class="card">
            <img src="uploads/<?= htmlspecialchars($row['imagen']) ?>" class="card-img-top" alt="Servicio">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($row['titulo']) ?></h5>
              <p class="card-text text-muted"><?= substr(htmlspecialchars($row['descripcion']), 0, 80) ?>...</p>
              <a href="detalle_servicio.php?id=<?= $row['id'] ?>" class="btn btn-primary w-100">Ver más</a>
            </div>
          </div>
        </div>
      <?php } ?>

    </div>
  </div>

  
  <footer>
    <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($config['nombre_sitio']) ?> - Todos los derechos reservados</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>