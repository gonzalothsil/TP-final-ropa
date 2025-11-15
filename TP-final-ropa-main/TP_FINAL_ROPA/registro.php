<?php
include("conexion.php");

if (isset($_POST['registro'])) {
  $nombre = trim($_POST['nombre']);
  $apellido = trim($_POST['apellido']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  // Validar formato del email
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "⚠️ Email no válido.";
  } 
  // Verificar si el email ya está registrado
  elseif ($conn->query("SELECT id FROM usuarios WHERE email = '$email'")->num_rows > 0) {
    $error = "⚠️ Este correo ya está registrado.";
  } 
  // Validar contraseña segura
elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
    $error = "⚠️ La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número.";
  } 
  else {
    // Encriptar y guardar
    $pass_hash = password_hash($password, PASSWORD_BCRYPT);
    $sql = "INSERT INTO usuarios (nombre, apellido, email, password) VALUES ('$nombre','$apellido','$email','$pass_hash')";
    if ($conn->query($sql)) {
      header("Location: login.php");
      exit;
    } else {
      $error = "❌ Error al registrar usuario.";
    }
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
<body class="bg-light">
  <div class="container py-5">
    <h2 class="text-center mb-4">Crear Cuenta</h2>
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card p-4 shadow-sm">
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Nombre</label>
              <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Apellido</label>
              <input type="text" name="apellido" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Correo electrónico</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Contraseña</label>
              <input type="password" name="password" class="form-control" 
                     placeholder="Debe tener 8+ caracteres, mayúscula, número y símbolo" required>
            </div>
            <button type="submit" name="registro" class="btn btn-success w-100">Registrarse</button>
            <?php if (isset($error)) echo "<p class='text-danger mt-3 text-center'>$error</p>"; ?>
          </form>
          <p class="text-center mt-3">¿ya tienes una cuenta? <a href="login.php">Inicia sesion</a></p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
