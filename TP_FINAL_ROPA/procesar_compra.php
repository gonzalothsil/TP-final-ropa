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
$cupon = trim($_POST['cupon'] ?? ''); // 🆕 cupón ingresado

if (!$nombre || !$direccion || !$telefono || !$pago) {
    echo "<script>alert('⚠️ Completa todos los campos antes de continuar.'); window.location='confirmar_compra.php';</script>";
    exit();
}

// 📞 Validación estricta del teléfono
if (!preg_match('/^[0-9]{10,11}$/', $telefono)) {
    echo "<script>alert('⚠️ El número de teléfono debe tener 10 u 11 dígitos reales.'); window.location='confirmar_compra.php';</script>";
    exit();
}

// ================================
//    OBTENER TOTAL DEL CARRITO
// ================================
$total_query = $conn->query("
  SELECT SUM(ci.cantidad * p.precio) AS total
  FROM carrito_items ci
  INNER JOIN productos p ON ci.producto_id = p.id
  INNER JOIN carritos c ON ci.carrito_id = c.id
  WHERE c.usuario_id = $usuario_id
");

$total_original = floatval($total_query->fetch_assoc()['total'] ?? 0);

if ($total_original <= 0) {
    echo "<script>alert('Tu carrito está vacío.'); window.location='productos.php';</script>";
    exit();
}

$total_final = $total_original;
$descuento_aplicado = 0;

// ================================
//    VALIDACIÓN DEL CUPÓN
// ================================
if (!empty($cupon)) {
    $stmt = $conn->prepare("SELECT descuento, habilitado FROM cupones WHERE codigo = ?");
    $stmt->bind_param("s", $cupon);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {

        if ($row['habilitado'] == 1) {
            $descuento = floatval($row['descuento']);
            $descuento_aplicado = ($total_original * ($descuento / 100));
            $total_final = $total_original - $descuento_aplicado;
        } else {
            echo "<script>alert('⚠️ Este cupón está deshabilitado.'); window.location='confirmar_compra.php';</script>";
            exit();
        }

    } else {
        echo "<script>alert('⚠️ Cupón inválido.'); window.location='confirmar_compra.php';</script>";
        exit();
    }
}

// ================================
//      BAJAR STOCK
// ================================
$items = $conn->query("
  SELECT ci.producto_id, ci.cantidad
  FROM carrito_items ci
  INNER JOIN carritos c ON ci.carrito_id = c.id
  WHERE c.usuario_id = $usuario_id
");

while ($item = $items->fetch_assoc()) {
    $producto_id = $item['producto_id'];
    $cantidad = $item['cantidad'];

    $conn->query("UPDATE productos SET stock = stock - $cantidad WHERE id = $producto_id");
}

// ================================
//      REGISTRAR COMPRA
// ================================
$metodo_pago = ($pago === "mp") ? "mercado_pago" : "tarjeta";

$conn->query("
    INSERT INTO compras (usuario_id, total, fecha, nombre, direccion, telefono, metodo_pago, cupon, descuento)
    VALUES ($usuario_id, $total_final, NOW(), '$nombre', '$direccion', '$telefono', '$metodo_pago', '$cupon', $descuento_aplicado)
");

$compra_id = $conn->insert_id;

// ================================
//      VACIAR CARRITO
// ================================
$conn->query("
    DELETE ci FROM carrito_items ci
    INNER JOIN carritos c ON ci.carrito_id = c.id
    WHERE c.usuario_id = $usuario_id
");

// ================================
//      REDIRECCIÓN FINAL
// ================================
if ($pago === "mp") {
    echo "<script>
            alert('Compra realizada con éxito. Pago con Mercado Pago confirmado.');
            window.location='index.php';
          </script>";
    exit();
}

echo "<script>
        alert('Compra realizada con tarjeta con éxito.');
        window.location='index.php';
      </script>";
exit();

?>
