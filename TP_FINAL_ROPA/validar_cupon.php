<?php
session_start();
include("conexion.php");

$cupon = trim($_POST['cupon'] ?? "");

$usuario_id = $_SESSION['usuario_id'];

// Total original
$query = $conn->query("
    SELECT SUM(ci.cantidad * p.precio) AS total
    FROM carrito_items ci
    INNER JOIN productos p ON ci.producto_id = p.id
    INNER JOIN carritos c ON ci.carrito_id = c.id
    WHERE c.usuario_id = $usuario_id
");

$row = $query->fetch_assoc();
$total = floatval($row['total'] ?? 0);

// Verificar cupon
$q = $conn->query("SELECT * FROM cupones WHERE codigo = '$cupon' AND habilitado = 1 LIMIT 1");

if ($q->num_rows == 0) {
    echo json_encode(["valido" => false]);
    exit;
}

$c = $q->fetch_assoc();
$descuento = floatval($c['descuento']); // %

// Total con descuento
$total_desc = $total - ($total * ($descuento / 100));

echo json_encode([
    "valido" => true,
    "descuento" => $descuento,
    "total_descuento" => $total_desc
]);
