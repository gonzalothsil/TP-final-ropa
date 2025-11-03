<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">Pilchex</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link" href="productos.php">Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="carrito.php">Carrito 🛒</a></li>

        <?php if (isset($_SESSION['usuario'])): ?>
          <li class="nav-item">
            <span class="nav-link text-success fw-bold">👤 <?= htmlspecialchars($_SESSION['usuario']) ?></span>
          </li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Cerrar sesión</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Iniciar sesión</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Marquesina -->
<div class="marquesina">
    <span>🧾 MÍNIMO DE COMPRA $100.000 - PILCHEX Mayorista DE ROPA 🧾</span>
  </div>
