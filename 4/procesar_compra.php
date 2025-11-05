<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Acceso inválido'); window.location='carrito.php';</script>";
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nombre = $_POST['nombre'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$pago = $_POST['pago'] ?? '';

if (!$nombre || !$direccion || !$telefono || !$pago) {
    echo "<script>alert('⚠️ Completa todos los campos antes de continuar.'); window.location='confirmar_compra.php';</script>";
    exit();
}

// Obtener total desde la base de datos
$total_query = $conn->query("
  SELECT SUM(ci.cantidad * p.precio) AS total
  FROM carrito_items ci
  INNER JOIN productos p ON ci.producto_id = p.id
  INNER JOIN carritos c ON ci.carrito_id = c.id
  WHERE c.usuario_id = $usuario_id
");

$total = floatval($total_query->fetch_assoc()['total'] ?? 0);

if ($total <= 0) {
    echo "<script>alert('Tu carrito está vacío.'); window.location='productos.php';</script>";
    exit();
}

// 💳 Si eligió Mercado Pago
if ($pago === 'mp') {
    // Convertir el total al formato de MP (sin decimales)
    $monto = number_format($total, 2, '.', '');

    // Alias de MP (reemplazalo por el tuyo)
    $alias = "gonzalo.tho";

    // Enlace de pago directo (usando link de alias)
    $link = "https://www.mercadopago.com.ar/pay?alias=$alias&amount=$monto&description=Compra%20Pilchex";

    // Redirigir al usuario al pago
    header("Location: $link");
    exit();
}

// 🧾 Si eligió tarjeta (o cualquier otro método interno)
$conn->query("INSERT INTO compras (usuario_id, total, fecha, nombre, direccion, telefono, metodo_pago) 
              VALUES ($usuario_id, $total, NOW(), '$nombre', '$direccion', '$telefono', 'tarjeta')");
$compra_id = $conn->insert_id;

echo "<script>alert('Compra con tarjeta registrada correctamente.'); window.location='ticket.php?id=$compra_id';</script>";
exit();
