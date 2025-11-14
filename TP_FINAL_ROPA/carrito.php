<?php
session_start();
include("conexion.php");

// Si no hay usuario logueado, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// ✅ Buscar o crear el carrito del usuario en la base
$carrito_query = $conn->query("SELECT id FROM carritos WHERE usuario_id = $usuario_id");
if ($carrito_query->num_rows > 0) {
    $carrito_id = $carrito_query->fetch_assoc()['id'];
} else {
    $conn->query("INSERT INTO carritos (usuario_id) VALUES ($usuario_id)");
    $carrito_id = $conn->insert_id;
}

// ✅ Eliminar producto del carrito
if (isset($_POST['eliminar'])) {
    $producto_id = $_POST['id'];
    $conn->query("DELETE FROM carrito_items WHERE carrito_id = $carrito_id AND producto_id = $producto_id");
}

// ✅ Obtener los productos del carrito
$query = "
    SELECT p.id, p.nombre, p.precio, ci.cantidad
    FROM carrito_items ci
    JOIN productos p ON ci.producto_id = p.id
    WHERE ci.carrito_id = $carrito_id
";
$resultado = $conn->query($query);

// ✅ Calcular total
$total = 0;
$carrito = [];
while ($row = $resultado->fetch_assoc()) {
    $carrito[] = $row;
    $total += $row['precio'] * $row['cantidad'];
}

// ✅ Generar texto para reclamo por WhatsApp
$mensaje_reclamo = "Hola, soy " . $_SESSION['usuario_nombre'] . ". Quisiera hacer un reclamo sobre mi carrito:\n\n";
foreach ($carrito as $item) {
    $mensaje_reclamo .= "- {$item['nombre']} (x{$item['cantidad']}) - $" . number_format($item['precio'] * $item['cantidad'], 2) . "\n";
}
$mensaje_reclamo .= "\nTotal: $" . number_format($total, 2);
$mensaje_reclamo = urlencode($mensaje_reclamo);

// ✅ Número de WhatsApp de atención (personalizá este)
$numero_whatsapp = "5491123456789"; // ← CAMBIA por tu número real (con código país)
?>

<!DOCTYPE html>
<html lang="es">
 <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Carrito - Pilchex</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">

  <style>
      body {
          display: flex;
          flex-direction: column;
          min-height: 100vh;
      }
      footer {
          margin-top: auto;
      }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
        <img src="pilchex.png" alt="Logo Pilchex">
        <span>Pilchex</span>
      </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="productos.php">Catálogo</a></li>
        <li class="nav-item"><a class="nav-link active" href="carrito.php">Carrito</a></li>

        <?php if (isset($_SESSION['usuario_id'])): ?>
          <?php if ($_SESSION['usuario_rol'] === 'empleado' || $_SESSION['usuario_rol'] === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="panel_empleado.php">Panel</a></li>
          <?php endif; ?>
          <li class="nav-item">
            <a class="nav-link text-success fw-bold" href="#">👤 <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></a>
          </li>
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
  <h2 class="text-center mb-4">🛒 Carrito de Compras</h2>

  <?php if (empty($carrito)): ?>
    <div class="alert alert-warning text-center">Tu carrito está vacío.</div>
    <div class="text-center">
      <a href="productos.php" class="btn btn-primary">Ver productos</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-striped text-center align-middle">
        <thead class="table-dark">
          <tr>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio Unitario</th>
            <th>Total</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($carrito as $item): ?>
            <tr>
              <td><?= htmlspecialchars($item['nombre']) ?></td>
              <td><?= $item['cantidad'] ?></td>
              <td>$<?= number_format($item['precio'], 2) ?></td>
              <td>$<?= number_format($item['precio'] * $item['cantidad'], 2) ?></td>
              <td>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="id" value="<?= $item['id'] ?>">
                  <button type="submit" name="eliminar" class="btn btn-danger btn-sm">Eliminar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="text-end mt-3">
      <h5>Total de la compra: <span class="text-primary fw-bold">$<?= number_format($total, 2) ?></span></h5>

      <?php if ($total < 100000): ?>
        <div class="alert alert-danger mt-3 text-center">
          ⚠️ El mínimo de compra es de <strong>$100.000</strong>. Agregá más productos para continuar.
        </div>
        <div class="text-center">
          <a href="productos.php" class="btn btn-outline-primary">Seguir comprando</a>
        </div>
      <?php else: ?>
        <div class="text-center mt-4 d-flex justify-content-center gap-3 flex-wrap">
          <form method="POST" action="procesar_compra.php">
            <button type="submit" class="btn btn-success btn-lg">Finalizar compra</button>
          </form>
          <a href="https://wa.me/<?= $numero_whatsapp ?>?text=<?= $mensaje_reclamo ?>" 
             target="_blank" class="btn btn-outline-success btn-lg">
            📞 Hacer reclamo por WhatsApp
          </a>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<footer class="text-center">
    © 2025 Pilchex Mayorista - Todos los derechos reservados
</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
