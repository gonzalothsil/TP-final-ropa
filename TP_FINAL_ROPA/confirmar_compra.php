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

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script>
  function mostrarCamposPago() {
      const metodo = document.getElementById("pago").value;
      document.getElementById("tarjetaCampos").style.display = metodo === "tarjeta" ? "block" : "none";
      calcularDescuento();
  }

  function calcularDescuento() {
      const cupon = document.querySelector("input[name='cupon']").value;
      const metodo = document.querySelector("#pago").value;

      if (cupon === "" || metodo === "") {
          document.getElementById("precio-final").innerHTML = "Total: $<?= number_format($total, 2) ?>";
          document.getElementById("btn-confirmar").innerHTML = "Confirmar compra ($<?= number_format($total, 2) ?>)";
          return;
      }

      $.post("validar_cupon.php", {
          cupon: cupon,
          metodo: metodo,
          usuario_id: <?= $usuario_id ?>
      }, function(data) {

          let r = JSON.parse(data);

          document.getElementById("mensaje-cupon").innerHTML = r.mensaje;

          if (r.status === "ok") {
              document.getElementById("precio-final").innerHTML =
                  "Total con descuento: <b>$" + r.total_final.toFixed(2) + "</b>";

              document.getElementById("btn-confirmar").innerHTML =
                  "Confirmar compra ($" + r.total_final.toFixed(2) + ")";
          } else {
              document.getElementById("precio-final").innerHTML =
                  "Total: $<?= number_format($total, 2) ?>";

              document.getElementById("btn-confirmar").innerHTML =
                  "Confirmar compra ($<?= number_format($total, 2) ?>)";
          }
      });
  }
  </script>
</head>
<body class="bg-light">

<div class="container py-5">
  <div class="card shadow p-4 mx-auto" style="max-width: 600px;">

    <a href="carrito.php" class="btn btn-secondary mb-3">⬅ Volver al carrito</a>

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

      <!-- CUPÓN DE DESCUENTO -->
<div class="mb-3">
  <label class="form-label">Cupón de descuento</label>

  <div class="input-group">
    <input type="text" id="cupon" name="cupon" class="form-control" placeholder="Ej: PILCHE10">
    <button type="button" class="btn btn-success" onclick="aplicarCupon()">Aplicar</button>
  </div>

  <small id="mensajeCupon" class="text-danger"></small>
</div>

<!-- MOSTRAR PRECIO ACTUALIZADO -->
<div class="alert alert-info text-center">
  <b>Total actual:</b> $<span id="precioActual"><?= number_format($total, 2) ?></span>
</div>

<input type="hidden" id="totalHidden" name="total_final" value="<?= $total ?>">


      <div class="mb-3">
        <label class="form-label">Método de pago</label>
        <select name="pago" id="pago" class="form-select" required onchange="mostrarCamposPago()">
          <option value="">Seleccionar...</option>
          <option value="tarjeta">💳 Tarjeta</option>
          <option value="mp">💸 Mercado Pago</option>
        </select>
      </div>

      <!-- Tarjeta -->
      <div id="tarjetaCampos" style="display:none;">
        <div class="mb-3">
          <label class="form-label">Número de tarjeta</label>
          <input type="text" name="tarjeta_num" class="form-control" maxlength="16">
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Vencimiento</label>
            <input type="text" name="tarjeta_venc" class="form-control">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">CVV</label>
            <input type="text" name="tarjeta_cvv" class="form-control" maxlength="3">
          </div>
        </div>
      </div>

      <input type="hidden" name="total_original" value="<?= $total ?>">

      <p id="precio-final" class="mt-3 fs-5 fw-bold">Total: $<?= number_format($total, 2) ?></p>

      <div class="text-center mt-4">
        <button type="submit" id="btn-confirmar" class="btn btn-primary w-100">
          Confirmar compra ($<?= number_format($total, 2) ?>)
        </button>
      </div>

    </form>
  </div>
</div>
<script>
function aplicarCupon() {
    const cupon = document.getElementById("cupon").value.trim();
    const mensaje = document.getElementById("mensajeCupon");
    const precioActual = document.getElementById("precioActual");
    const totalHidden = document.getElementById("totalHidden");

    if (cupon === "") {
        mensaje.textContent = "Ingresá un cupón primero.";
        return;
    }

    // Enviar AJAX al backend para validar el cupón
    fetch("validar_cupon.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "cupon=" + encodeURIComponent(cupon)
    })
    .then(r => r.json())
    .then(data => {
        if (data.valido) {
            mensaje.textContent = "Cupón aplicado: -" + data.descuento + "%";
            mensaje.classList.remove("text-danger");
            mensaje.classList.add("text-success");

            // Calcular nuevo precio
            let nuevoPrecio = data.total_descuento;

            precioActual.textContent = nuevoPrecio.toFixed(2);
            totalHidden.value = nuevoPrecio;
        } else {
            mensaje.textContent = "Cupón inválido o no disponible.";
            mensaje.classList.remove("text-success");
            mensaje.classList.add("text-danger");
        }
    });
}
</script>

</body>
</html>
