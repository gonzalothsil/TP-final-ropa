<?php
include("conexion.php");
session_start();

// Si ya está logueado, redirige
if (isset($_SESSION['usuario_id'])) {
  header("Location: index.php");
  exit;
}

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $sql = "SELECT * FROM usuarios WHERE email = '$email' LIMIT 1";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
      $_SESSION['usuario_id'] = $user['id'];
      $_SESSION['usuario_nombre'] = $user['nombre'];
      $_SESSION['usuario_rol'] = $user['rol'];

      header("Location: productos.php");
      exit;
    } else {
      $mensaje = "⚠️ Contraseña incorrecta.";
    }
  } else {
    $mensaje = "⚠️ Usuario no encontrado.";
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar Sesión - Pilchex</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid px-3">
      <a class="navbar-brand d-flex align-items-center" href="index.php">
        <img src="pilchex.png" alt="Logo Pilchex">
        <span>Pilchex</span>
      </a>
    </div>
  </nav>

  <!-- Contenido principal -->
  <div class="container py-5">
    <h2 class="text-center mb-4">Iniciar Sesión</h2>
    <?php if ($mensaje): ?>
      <div class="alert alert-danger text-center"><?= $mensaje ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card shadow-sm p-4">
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Correo electrónico</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Contraseña</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
          </form>
          <p class="text-center mt-3">¿No tenés cuenta? <a href="registro.php">Registrate</a></p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
