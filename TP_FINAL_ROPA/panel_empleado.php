<?php
// panel.php - Panel de admin (productos + cupones) - Hecho para NO romper el diseño
include("conexion.php");
session_start();

// Acceso: solo admin
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
$rol = trim(strtolower($_SESSION['usuario_rol'] ?? ''));
if ($rol !== 'admin') {
    header("Location: index.php");
    exit;
}

// Mensajes para mostrar en HTML (no imprimir nada antes del DOCTYPE)
$mensaje = null;

// -----------------------------
// CRUD PRODUCTOS
// -----------------------------
if (isset($_POST['agregar_producto'])) {
    $nombre = mysqli_real_escape_string($conn, trim($_POST['nombre'] ?? ''));
    $precio = floatval($_POST['precio'] ?? 0);
    $stock  = intval($_POST['stock'] ?? 0);
    $color  = mysqli_real_escape_string($conn, trim($_POST['color'] ?? ''));
    $talle  = mysqli_real_escape_string($conn, trim($_POST['talle'] ?? ''));
    $imagen = mysqli_real_escape_string($conn, trim($_POST['imagen'] ?? ''));

    if ($nombre !== '' && $precio > 0) {
        $sql = "INSERT INTO productos (nombre, precio, stock, imagen, color, talle)
                VALUES ('$nombre', $precio, $stock, '$imagen', '$color', '$talle')";
        if ($conn->query($sql)) {
            $mensaje = "✅ Producto agregado con éxito.";
        } else {
            $mensaje = "❌ Error al agregar producto: " . $conn->error;
        }
    } else {
        $mensaje = "⚠️ Completa los campos Nombre y Precio correctamente.";
    }
}

if (isset($_POST['actualizar_stock'])) {
    $id = intval($_POST['id'] ?? 0);
    $nuevo_stock = intval($_POST['nuevo_stock'] ?? 0);
    if ($id > 0) {
        $conn->query("UPDATE productos SET stock = $nuevo_stock WHERE id = $id");
        $mensaje = "✅ Stock actualizado.";
    }
}

if (isset($_POST['eliminar_producto'])) {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        $conn->query("DELETE FROM productos WHERE id = $id");
        $mensaje = "🗑️ Producto eliminado.";
    }
}

// -----------------------------
// CRUD CUPONES
// -----------------------------
if (isset($_POST['crear_cupon'])) {
    $codigo = strtoupper(mysqli_real_escape_string($conn, trim($_POST['codigo'] ?? '')));
    $descuento = intval($_POST['descuento'] ?? 0);
    $metodo = mysqli_real_escape_string($conn, trim($_POST['metodo'] ?? 'ambos')); // mp / tarjeta / ambos

    if ($codigo !== '' && $descuento > 0 && $descuento <= 90 && in_array($metodo, ['mp', 'tarjeta', 'ambos'])) {
        $sql = "INSERT INTO cupones (codigo, descuento, metodo, habilitado)
                VALUES ('$codigo', $descuento, '$metodo', 1)";
        if ($conn->query($sql)) {
            $mensaje = "🎟️ Cupón creado correctamente.";
        } else {
            if (strpos($conn->error, 'Duplicate') !== false || strpos($conn->error, 'duplicate') !== false) {
                $mensaje = "⚠️ El código ya existe.";
            } else {
                $mensaje = "❌ Error al crear cupón: " . $conn->error;
            }
        }
    } else {
        $mensaje = "⚠️ Datos inválidos para el cupón.";
    }
}

if (isset($_POST['toggle_cupon'])) {
    $id = intval($_POST['id'] ?? 0);
    $estado = intval($_POST['estado'] ?? 0);
    $nuevo = $estado ? 0 : 1;
    if ($id > 0) {
        $conn->query("UPDATE cupones SET habilitado = $nuevo WHERE id = $id");
        $mensaje = $nuevo ? "🟢 Cupón habilitado." : "🔴 Cupón deshabilitado.";
    }
}

if (isset($_POST['eliminar_cupon'])) {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        $conn->query("DELETE FROM cupones WHERE id = $id");
        $mensaje = "🗑️ Cupón eliminado.";
    }
}

// Obtener datos para mostrar
$productos = $conn->query("SELECT * FROM productos ORDER BY id DESC");
$cupones = $conn->query("SELECT * FROM cupones ORDER BY id DESC");

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel Admin - Pilchex</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Ajustes menores para que todo encaje con tu estilo */
    .card-header .small { font-size: .9rem; color: #eee; }
    td img { object-fit: cover; }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container-fluid px-3">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="pilchex.png" alt="Logo" style="height:40px; margin-right:8px;">
      <span>Pilchex</span>
    </a>
    <div class="collapse navbar-collapse"></div>
    <ul class="navbar-nav ms-auto">
      <li class="nav-item"><a class="nav-link" href="productos.php">Catálogo</a></li>
      <li class="nav-item"><a class="nav-link" href="carrito.php">Carrito</a></li>
      <li class="nav-item"><a class="nav-link active" href="#">Panel</a></li>
      <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Cerrar sesión</a></li>
    </ul>
  </div>
</nav>

<div class="container py-4">
  <h2 class="text-center mb-4">Panel de Control - Productos & Cupones</h2>

  <?php if ($mensaje): ?>
    <div class="alert alert-info text-center"><?= htmlspecialchars($mensaje) ?></div>
  <?php endif; ?>

  <!-- AGREGAR PRODUCTO -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-dark text-white">➕ Agregar producto</div>
    <div class="card-body">
      <form method="POST" class="row g-3">
        <div class="col-md-3">
          <input type="text" name="nombre" class="form-control" placeholder="Nombre" required>
        </div>
        <div class="col-md-2">
          <input type="number" step="0.01" name="precio" class="form-control" placeholder="Precio" required>
        </div>
        <div class="col-md-2">
          <input type="number" name="stock" class="form-control" placeholder="Stock" required>
        </div>
        <div class="col-md-2">
          <input type="text" name="color" class="form-control" placeholder="Color" required>
        </div>
        <div class="col-md-2">
          <input type="text" name="talle" class="form-control" placeholder="Talle" required>
        </div>
        <div class="col-md-6 mt-2">
          <input type="text" name="imagen" class="form-control" placeholder="URL imagen (opcional)">
        </div>
        <div class="col-md-2 mt-2">
          <button type="submit" name="agregar_producto" class="btn btn-success w-100">Agregar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- TABLA PRODUCTOS -->
  <div class="card shadow-sm mb-5">
    <div class="card-header bg-secondary text-white">📋 Productos</div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped text-center align-middle">
          <thead class="table-dark">
            <tr>
              <th>ID</th><th>Img</th><th>Nombre</th><th>Color</th><th>Talle</th>
              <th>Precio</th><th>Stock</th><th>Actualizar</th><th>Eliminar</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($p = $productos->fetch_assoc()): ?>
            <tr>
              <td><?= $p['id'] ?></td>
              <td><img src="<?= htmlspecialchars($p['imagen']) ?>" alt="img" width="60" height="60"></td>
              <td><?= htmlspecialchars($p['nombre']) ?></td>
              <td><?= htmlspecialchars($p['color']) ?></td>
              <td><?= htmlspecialchars($p['talle']) ?></td>
              <td>$<?= number_format($p['precio'], 2) ?></td>
              <td><?= intval($p['stock']) ?></td>
              <td>
                <form method="POST" class="d-flex justify-content-center" style="gap:.5rem;">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <input type="number" name="nuevo_stock" min="0" class="form-control" placeholder="Nuevo" required style="width:90px;">
                  <button type="submit" name="actualizar_stock" class="btn btn-primary btn-sm">OK</button>
                </form>
              </td>
              <td>
                <form method="POST" onsubmit="return confirm('Eliminar producto #<?= $p['id'] ?>?');">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <button type="submit" name="eliminar_producto" class="btn btn-danger btn-sm">X</button>
                </form>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- CREAR CUPÓN -->
  <div class="card mb-3 shadow-sm">
    <div class="card-header bg-dark text-white">🎟️ Crear cupón de descuento</div>
    <div class="card-body">
      <form method="POST" class="row g-3 align-items-center">
        <div class="col-md-3">
          <input type="text" name="codigo" class="form-control" placeholder="Código (EJ: PILCHE20)" required>
        </div>
        <div class="col-md-2">
          <input type="number" name="descuento" class="form-control" placeholder="% desc" min="1" max="90" required>
        </div>
        <div class="col-md-3">
          <select name="metodo" class="form-control" required>
            <option value="ambos">Válido para ambos</option>
            <option value="mp">Solo Mercado Pago</option>
            <option value="tarjeta">Solo Tarjeta</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" name="crear_cupon" class="btn btn-success w-100">Crear cupón</button>
        </div>
      </form>
    </div>
  </div>

  <!-- LISTA CUPONES -->
  <div class="card shadow-sm mb-5">
    <div class="card-header bg-secondary text-white">📄 Lista de cupones</div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped text-center align-middle">
          <thead class="table-dark">
            <tr>
              <th>ID</th><th>Código</th><th>Descuento</th><th>Método</th><th>Estado</th><th>Acción</th><th>Eliminar</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($c = $cupones->fetch_assoc()): ?>
            <tr>
              <td><?= $c['id'] ?></td>
              <td><strong><?= htmlspecialchars($c['codigo']) ?></strong></td>
              <td><?= intval($c['descuento']) ?>%</td>
              <td><?= ($c['metodo'] === 'mp' ? 'Mercado Pago' : ($c['metodo'] === 'tarjeta' ? 'Tarjeta' : 'Ambos')) ?></td>
              <td><?= $c['habilitado'] ? '🟢 Activo' : '🔴 Inactivo' ?></td>
              <td>
                <form method="POST" style="display:inline-block;">
                  <input type="hidden" name="id" value="<?= $c['id'] ?>">
                  <input type="hidden" name="estado" value="<?= $c['habilitado'] ?>">
                  <button type="submit" name="toggle_cupon" class="btn btn-warning btn-sm">
                    <?= $c['habilitado'] ? 'Desactivar' : 'Activar' ?>
                  </button>
                </form>
              </td>
              <td>
                <form method="POST" onsubmit="return confirm('Eliminar cupón <?= htmlspecialchars($c['codigo']) ?>?');" style="display:inline-block;">
                  <input type="hidden" name="id" value="<?= $c['id'] ?>">
                  <button type="submit" name="eliminar_cupon" class="btn btn-danger btn-sm">X</button>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
