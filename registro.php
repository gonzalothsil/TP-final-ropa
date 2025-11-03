<?php
include("conexion.php");

if (isset($_POST['registro'])) {
  $nombre = $_POST['nombre'];
  $apellido = $_POST['apellido'];
  $email = $_POST['email'];
  $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);

  $sql = "INSERT INTO usuarios (nombre, apellido, email, password) VALUES ('$nombre','$apellido','$email','$pass')";
  if ($conn->query($sql)) {
    header("Location: login.php");
    exit;
  } else {
    $error = "Error al registrar usuario.";
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro - Pilchex</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-5">
    <h2 class="text-center mb-4">Crear Cuenta</h2>
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card p-4 shadow-sm">
          <form method="POST">
            <div class="mb-3">
              <label>Nombre</label>
              <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Apellido</label>
              <input type="text" name="apellido" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Contraseña</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="registro" class="btn btn-success w-100">Registrarse</button>
            <?php if (isset($error)) echo "<p class='text-danger mt-2'>$error</p>"; ?>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
