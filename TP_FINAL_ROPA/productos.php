<?php
session_start();
include("conexion.php");

$usuario_logueado = isset($_SESSION['usuario_id']);
$usuario_id = $usuario_logueado ? $_SESSION['usuario_id'] : null;

// Crear carrito si no existe
if ($usuario_logueado) {
    $carrito_query = $conn->query("SELECT id FROM carritos WHERE usuario_id = $usuario_id");
    if ($carrito_query->num_rows > 0) {
        $carrito_id = $carrito_query->fetch_assoc()['id'];
    } else {
        $conn->query("INSERT INTO carritos (usuario_id) VALUES ($usuario_id)");
        $carrito_id = $conn->insert_id;
    }
}

// Agregar producto al carrito
if (isset($_POST['agregar'])) {
    if (!$usuario_logueado) {
        $mensaje = "⚠️ Debes iniciar sesión para agregar productos al carrito.";
    } else {
        $id = (int)$_POST['id'];
        $cantidad = max(1, (int)$_POST['cantidad']);
        $color = mysqli_real_escape_string($conn, $_POST['color']);
        $talle = mysqli_real_escape_string($conn, $_POST['talle']);

        // Verificar stock
        $stock_query = $conn->query("SELECT stock FROM productos WHERE id = $id");
        if ($stock_query->num_rows === 0) {
            $mensaje = "❌ Producto no encontrado.";
        } else {
            $stock = (int)$stock_query->fetch_assoc()['stock'];
            $existe = $conn->query("SELECT id, cantidad FROM carrito_items WHERE carrito_id = $carrito_id AND producto_id = $id");
            $cantidad_actual = 0;
            if ($existe->num_rows > 0) {
                $fila = $existe->fetch_assoc();
                $cantidad_actual = $fila['cantidad'];
            }

            if ($cantidad_actual + $cantidad > $stock) {
                $mensaje = "⚠️ Stock insuficiente. Solo hay $stock unidades disponibles.";
            } else {
                if ($existe->num_rows > 0) {
                    $nueva_cantidad = $cantidad_actual + $cantidad;
                    $conn->query("UPDATE carrito_items SET cantidad = $nueva_cantidad WHERE id = {$fila['id']}");
                } else {
                    $conn->query("INSERT INTO carrito_items (carrito_id, producto_id, cantidad) 
                                  VALUES ($carrito_id, $id, $cantidad)");
                }
                $mensaje = "🛒 Producto agregado al carrito.";
            }
        }
    }
}

$resultado = $conn->query("SELECT * FROM productos");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Productos - Pilchex Mayorista</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body>

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

        <?php if ($usuario_logueado): ?>
          <?php if ($_SESSION['usuario_rol'] === 'empleado' || $_SESSION['usuario_rol'] === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="panel_empleado.php">Panel</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link text-success fw-bold" href="#">👤 <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Cerrar Sesión</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Iniciar Sesión</a></li>
          <li class="nav-item"><a class="nav-link" href="registro.php">Registrarse</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="marquesina bg-dark text-white text-center py-2">
  <span>🧾 MÍNIMO DE COMPRA $100.000 - PILCHEX MAYORISTA DE ROPA 🧾 - ENVÍO A TODO EL PAÍS 🌐</span>
</div>

<div class="container py-5">
  <h2 class="text-center mb-4">Catálogo de Productos</h2>

  <?php if (isset($mensaje)): ?>
    <div class="alert alert-info text-center"><?= $mensaje ?></div>
  <?php endif; ?>

  <div class="row">
    <?php while ($row = $resultado->fetch_assoc()): ?>
      <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
          <?php
            $img = trim($row['imagen']);
            if (filter_var($img, FILTER_VALIDATE_URL)) {
                $src = $img;
            } elseif (!empty($img) && file_exists("uploads/$img")) {
                $src = "uploads/$img";
            } else {
                $src = "https://via.placeholder.com/300x200?text=Sin+Imagen";
            }
          ?>
          <img src="<?= htmlspecialchars($src) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['nombre']) ?>">

          <div class="card-body text-center">
            <h5 class="card-title"><?= htmlspecialchars($row['nombre']) ?></h5>
            <p class="card-text">$<?= number_format($row['precio'], 2) ?></p>
            <p class="text-muted mb-1">Stock disponible: <?= $row['stock'] ?></p>

            <!-- Mostrar color y talle -->
            <p class="text-muted mb-1">Color: <strong><?= htmlspecialchars($row['color']) ?></strong></p>
            <p class="text-muted mb-3">Talle: <strong><?= htmlspecialchars($row['talle']) ?></strong></p>

            <form method="POST" class="mt-2">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">

              <!-- Selección de color -->
              <select name="color" class="form-select mb-2" required>
                <option value="<?= htmlspecialchars($row['color']) ?>"><?= htmlspecialchars($row['color']) ?></option>
                <option value="Negro">Negro</option>
                <option value="Blanco">Blanco</option>
                <option value="Gris">Gris</option>
                <option value="Rojo">Rojo</option>
                <option value="Azul">Azul</option>
              </select>

              <!-- Selección de talle -->
              <select name="talle" class="form-select mb-3" required>
                <option value="<?= htmlspecialchars($row['talle']) ?>"><?= htmlspecialchars($row['talle']) ?></option>
                <option value="S">S</option>
                <option value="M">M</option>
                <option value="L">L</option>
                <option value="XL">XL</option>
              </select>

              <div class="mb-2">
                <input type="number" name="cantidad" value="1" min="1" max="<?= $row['stock'] ?>" class="form-control text-center" style="width:80px;margin:auto;">
              </div>

              <?php if ($row['stock'] > 0): ?>
                <?php if ($usuario_logueado): ?>
                  <button type="submit" name="agregar" class="btn btn-dark w-100">
                    Agregar al carrito
                  </button>
                <?php else: ?>
                  <a href="login.php" class="btn btn-secondary w-100">Inicia sesión para comprar</a>
                <?php endif; ?>
              <?php else: ?>
                <button class="btn btn-secondary w-100" disabled>Sin stock</button>
              <?php endif; ?>
            </form>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<footer class="bg-dark text-white text-center py-3 mt-5">
  © 2025 Pilchex Mayorista - Todos los derechos reservados
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
