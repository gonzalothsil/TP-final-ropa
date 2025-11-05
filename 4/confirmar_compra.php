<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['usuario_id'])) {
  header("Location: login.php");
  exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Calcular total actual del carrito
$query = $conn->query("
  SELECT SUM(ci.cantidad * p.precio) AS total
  FROM carrito_items ci
  INNER JOIN productos p ON ci.producto_id = p.id
  INNER JOIN carritos c ON ci.carrito_id = c.id
  WHERE c.usuario_id = $usuario_id
");

$row = $query->fetch_assoc();
$total = $row['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Confirmar compra</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script>
  function mostrarCamposPago() {
      const metodo = document.getElementById("pago").value;
      document.getElementById("tarjetaCampos").style.display = metodo === "tarjeta" ? "block" : "none";
  }
  </script>
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="card shadow p-4 mx-auto" style="max-width: 600px;">
      <h2 class="mb-4 text-center">🧾 Confirmar compra</h2>
      <form action="procesar_compra.php" method="POST">
        <div class="mb-3">
          <label class="form-label">Nombre completo</label>
          <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Dirección</label>
          <input type="text" name="direccion" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Teléfono</label>
          <input type="text" name="telefono" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Método de pago</label>
          <select name="pago" id="pago" class="form-select" required onchange="mostrarCamposPago()">
            <option value="">Seleccionar...</option>
            <option value="tarjeta">💳 Tarjeta de crédito / débito</option>
            <option value="mp">💸 Mercado Pago</option>
          </select>
        </div>

        <!-- Campos tarjeta -->
        <div id="tarjetaCampos" style="display:none;">
          <div class="mb-3">
            <label class="form-label">Número de tarjeta</label>
            <input type="text" name="tarjeta_num" class="form-control" pattern="\d{16}" maxlength="16">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Vencimiento (MM/AA)</label>
              <input type="text" name="tarjeta_venc" class="form-control" pattern="\d{2}/\d{2}">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">CVV</label>
              <input type="text" name="tarjeta_cvv" class="form-control" pattern="\d{3}" maxlength="3">
            </div>
          </div>
        </div>

        <input type="hidden" name="total" value="<?= $total ?>">

        <div class="text-center mt-4">
          <button type="submit" class="btn btn-primary w-100">
            Confirmar compra ($<?= number_format($total, 2) ?>)
          </button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
