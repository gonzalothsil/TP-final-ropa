<?php
session_start();
include("conexion.php");

// Verificar acceso
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin', 'empleado'])) {
    header("Location: login.php");
    exit();
}

// ✅ AGREGAR NUEVO PRODUCTO
if (isset($_POST['agregar_producto'])) {
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $talle = mysqli_real_escape_string($conn, $_POST['talle']);
    $imagen = trim($_POST['imagen']);

    if (!empty($nombre) && $precio > 0 && !empty($color) && !empty($talle)) {
        $conn->query("INSERT INTO productos (nombre, precio, stock, imagen, color, talle) 
                      VALUES ('$nombre', $precio, $stock, '$imagen', '$color', '$talle')");
        $mensaje = "✅ Producto agregado con éxito.";
    } else {
        $mensaje = "⚠️ Completa todos los campos correctamente.";
    }
}

// ✅ ACTUALIZAR STOCK
if (isset($_POST['actualizar_stock'])) {
    $id = intval($_POST['id']);
    $nuevo_stock = intval($_POST['nuevo_stock']);
    $conn->query("UPDATE productos SET stock = $nuevo_stock WHERE id = $id");
    $mensaje = "✅ Stock actualizado.";
}

// ✅ ELIMINAR PRODUCTO
if (isset($_POST['eliminar_producto'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM productos WHERE id = $id");
    $mensaje = "🗑️ Producto eliminado.";
}

// Obtener lista de productos
$productos = $conn->query("SELECT * FROM productos ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel de Empleado - Pilchex</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container-fluid px-3">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="pilchex.png" alt="Logo Pilchex">
      <span>Pilchex</span>
    </a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="productos.php">Catálogo</a></li>
        <li class="nav-item"><a class="nav-link" href="carrito.php">Carrito</a></li>
        <li class="nav-item"><a class="nav-link active" href="#">Panel</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Cerrar sesión</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h2 class="text-center mb-4"> Panel de Control - Productos</h2>

  <?php if (isset($mensaje)): ?>
    <div class="alert alert-info text-center"><?= $mensaje ?></div>
  <?php endif; ?>

  <!-- Formulario agregar producto -->
  <div class="card mb-5 shadow-sm">
    <div class="card-header bg-dark text-white">➕ Agregar nuevo producto</div>
    <div class="card-body">
      <form method="POST">
        <div class="row g-3">
          <div class="col-md-3">
            <input type="text" name="nombre" class="form-control" placeholder="Nombre del producto" required>
          </div>
          <div class="col-md-2">
            <input type="number" step="0.01" name="precio" class="form-control" placeholder="Precio" required>
          </div>
          <div class="col-md-2">
            <input type="number" name="stock" class="form-control" placeholder="Stock inicial" required>
          </div>
          <div class="col-md-2">
            <input type="text" name="color" class="form-control" placeholder="Color" required>
          </div>
          <div class="col-md-2">
            <input type="text" name="talle" class="form-control" placeholder="Talle (S, M, L, XL...)" required>
          </div>
          <div class="col-md-6 mt-2">
            <input type="text" name="imagen" class="form-control" placeholder="URL de imagen (opcional)">
          </div>
          <div class="col-md-2 mt-2">
            <button type="submit" name="agregar_producto" class="btn btn-success w-100">Agregar</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Tabla de productos -->
  <div class="card shadow-sm">
    <div class="card-header bg-secondary text-white">📋 Lista de productos</div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped text-center align-middle">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Imagen</th>
              <th>Nombre</th>
              <th>Color</th>
              <th>Talle</th>
              <th>Precio</th>
              <th>Stock</th>
              <th>Actualizar stock</th>
              <th>Eliminar</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($p = $productos->fetch_assoc()): ?>
              <tr>
                <td><?= $p['id'] ?></td>
                <td><img src="<?= htmlspecialchars($p['imagen']) ?>" alt="img" width="60"></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= htmlspecialchars($p['color']) ?></td>
                <td><?= htmlspecialchars($p['talle']) ?></td>
                <td>$<?= number_format($p['precio'], 2) ?></td>
                <td><?= $p['stock'] ?></td>
                <td>
                  <form method="POST" class="d-flex justify-content-center">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <input type="number" name="nuevo_stock" min="0" class="form-control w-50 me-2" placeholder="Nuevo stock" required>
                    <button type="submit" name="actualizar_stock" class="btn btn-primary btn-sm">Actualizar</button>
                  </form>
                </td>
                <td>
                  <form method="POST">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" name="eliminar_producto" class="btn btn-danger btn-sm">Eliminar</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<footer class="bg-dark text-white text-center py-3 mt-5">
  © 2025 Pilchex Mayorista - Todos los derechos reservados
</footer>

</body>
</html>
