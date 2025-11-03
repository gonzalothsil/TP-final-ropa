<?php
session_start();
include("conexion.php");

// 🔒 Verificar login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar el carrito del usuario
$carrito_query = $conn->query("SELECT id FROM carritos WHERE usuario_id = $usuario_id");
if ($carrito_query->num_rows === 0) {
    echo "<script>alert('No tienes productos en el carrito.'); window.location='carrito.php';</script>";
    exit();
}

$carrito_id = $carrito_query->fetch_assoc()['id'];

// Obtener los productos del carrito
$items = $conn->query("
    SELECT ci.producto_id, ci.cantidad, p.precio, p.stock
    FROM carrito_items ci
    INNER JOIN productos p ON ci.producto_id = p.id
    WHERE ci.carrito_id = $carrito_id
");

if ($items->num_rows === 0) {
    echo "<script>alert('Tu carrito está vacío.'); window.location='productos.php';</script>";
    exit();
}

// Calcular total y verificar stock
$total = 0;
while ($row = $items->fetch_assoc()) {
    if ($row['cantidad'] > $row['stock']) {
        echo "<script>alert('❌ No hay suficiente stock para uno de los productos.'); window.location='carrito.php';</script>";
        exit();
    }
    $total += $row['cantidad'] * $row['precio'];
}

// Verificar mínimo de compra
if ($total < 100000) {
    echo "<script>alert('⚠️ El mínimo de compra es $100.000.'); window.location='carrito.php';</script>";
    exit();
}

// Crear la compra
$conn->query("INSERT INTO compras (usuario_id, total, fecha) VALUES ($usuario_id, $total, NOW())");
$compra_id = $conn->insert_id;

// Insertar detalle de compra y descontar stock
$items->data_seek(0); // Reiniciar puntero del resultado
while ($row = $items->fetch_assoc()) {
    $producto_id = $row['producto_id'];
    $cantidad = $row['cantidad'];
    $precio = $row['precio'];

    $conn->query("INSERT INTO compra_detalle (compra_id, producto_id, cantidad, precio_unitario) 
                  VALUES ($compra_id, $producto_id, $cantidad, $precio)");

    $conn->query("UPDATE productos SET stock = GREATEST(stock - $cantidad, 0) WHERE id = $producto_id");
}

// Vaciar el carrito
$conn->query("DELETE FROM carrito_items WHERE carrito_id = $carrito_id");

// Mensaje de éxito
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Compra finalizada - Pilchex</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5 text-center">
    <div class="card shadow-sm p-4 mx-auto" style="max-width: 600px;">
      <h2 class="mb-3 text-success">✅ ¡Compra realizada con éxito!</h2>
      <p>Gracias por tu compra, <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>.</p>
      <p>Tu número de orden es: <strong>#<?= $compra_id ?></strong></p>
      <p>Total: <strong>$<?= number_format($total, 2) ?></strong></p>
      <a href="productos.php" class="btn btn-primary mt-3">Seguir comprando</a>
    </div>
  </div>
</body>
</html>
