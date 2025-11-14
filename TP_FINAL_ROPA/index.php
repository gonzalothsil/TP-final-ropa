<?php 
include("conexion.php"); 
session_start(); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pilchex - Mayorista de Ropa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>

<body>
  <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">


  <div class="container-fluid px-3">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="pilchex.png" alt="Logo Pilchex">
      <span>Pilchex</span>
    </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="productos.php">Catálogo</a></li>
          <li class="nav-item"><a class="nav-link" href="carrito.php">Carrito</a></li>

          <?php if (isset($_SESSION['usuario_id'])): ?>
            <li class="nav-item"><a class="nav-link" href="panel_empleado.php">Panel</a></li>
            <li class="nav-item"><a class="nav-link text-success fw-bold" href="#">👤 <?= $_SESSION['usuario_nombre'] ?></a></li>
            <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Cerrar Sesión</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="login.php">Iniciar Sesión</a></li>
            <li class="nav-item"><a class="nav-link" href="registro.php">Registrarse</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Marquesina -->
  <div class="marquesina">
  <span>🧾 MÍNIMO DE COMPRA $100.000 - PILCHEX MAYORISTA DE ROPA 🧾- ENVIO A TODO EL PAIS 🌐</span>
  </div>

  <!-- Hero -->

  <div class="banner-container text-center">
  <img src="banner.png" alt="Banner Pilchex" class="banner-img">
</div>
<header class="bg-light text-center py-5">
  <div class="container">
    <a href="productos.php" class="btn btn-primary btn-lg">Ver Catálogo</a>
  </div>
</header>


  <!-- Info -->
  <section class="container text-center py-5">
    <h2 class="mb-4 fw-semibold">¿Por qué elegirnos?</h2>
    <div class="row">
      <div class="col-md-4">
        <h5>🧵 Calidad garantizada</h5>
        <p>Materiales seleccionados y prendas duraderas.</p>
      </div>
      <div class="col-md-4">
        <h5>🚚 Envíos a todo el país</h5>
        <p>Rápido despacho y seguimiento de tus pedidos.</p>
      </div>
      <div class="col-md-4">
        <h5>💳 Compras seguras</h5>
        <p>Pagos protegidos y atención personalizada.</p>
      </div>
    </div>
  </section>

  <footer>
    <div class="container">
      <p>© 2025 Pilchex Mayorista - Todos los derechos reservados</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
