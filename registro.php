<?php
session_start();
include("conexion.php");

// Mostrar errores de PHP (solo en desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_documento = $_POST["cliente_tipo_documento_reg"];
    $numero_documento = $_POST["cliente_numero_documento_reg"];
    $nombre = $_POST["cliente_nombre_reg"];
    $apellido = $_POST["cliente_apellido_reg"];
    $telefono = $_POST["cliente_telefono_reg"];
    $provincia = $_POST["cliente_provincia_reg"];
    $ciudad = $_POST["cliente_ciudad_reg"];
    $direccion = $_POST["cliente_direccion_reg"];
    $correo = $_POST["cliente_email_reg"];
    $password1 = $_POST["cliente_clave_1_reg"];
    $password2 = $_POST["cliente_clave_2_reg"];

    if (!db_disponible()) {
        $mensaje = ['titulo' => 'Base de datos no disponible', 'texto' => 'El registro requiere una base de datos MySQL configurada.', 'tipo' => 'warning'];
    } elseif (
        empty($tipo_documento) || empty($numero_documento) || empty($nombre) || empty($apellido) ||
        empty($telefono) || empty($provincia) || empty($ciudad) || empty($direccion) ||
        empty($correo) || empty($password1) || empty($password2)
    ) {
        $mensaje = ['titulo' => 'Campos incompletos', 'texto' => 'Todos los campos son obligatorios.', 'tipo' => 'warning'];
    } elseif ($password1 !== $password2) {
        $mensaje = ['titulo' => 'Error', 'texto' => 'Las contraseñas no coinciden.', 'tipo' => 'error'];
    } else {
        $password_hashed = password_hash($password1, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO clientes (tipo_documento, numero_documento, nombre, apellido, telefono, provincia, ciudad, direccion, correo, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $tipo_documento, $numero_documento, $nombre, $apellido, $telefono, $provincia, $ciudad, $direccion, $correo, $password_hashed);

        if ($stmt->execute()) {
            $mensaje = ['titulo' => 'Registro exitoso', 'texto' => 'Tu cuenta ha sido creada correctamente', 'tipo' => 'success', 'redirigir' => 'login.php'];
        } else {
            $mensaje = ['titulo' => 'Error', 'texto' => 'Correo ya registrado o datos inválidos.', 'tipo' => 'error'];
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Registro - Restaurante</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Estilos -->
	<link rel="stylesheet" href="./css/normalize.css">
	<link rel="stylesheet" href="./css/mdb.min.css">
	<link rel="stylesheet" href="./css/all.css">
	<link rel="stylesheet" href="./css/style.css">
	<script src="./js/sweetalert2.js"></script>
</head>
<body>

<!-- Header -->
<header class="header full-box">
	<div class="header-brand text-center full-box">
		<a href="index.php">
			<img src="./assets/img/logo.png" alt="logo" class="img-fluid">
		</a>
	</div>

	<div class="header-options full-box">
		<nav class="header-navbar full-box poppins-regular font-weight-bold">
			<ul class="list-unstyled full-box">
				<li><a href="index.php">Inicio</a></li>
				<li><a href="menu.php">Menú</a></li>
			</ul>
		</nav>

		<!-- Botón login -->
		<div class="header-button full-box text-center btn-login-menu">
			<?php if (isset($_SESSION['cliente_nombre'])): ?>
				<i class="fas fa-user-circle" onclick="show_popup_login()" title="Mi cuenta"></i>
				<div class="div-bordered popup-login">
					<span class="text-center poppins-regular font-weight-bold">Hola, <?= htmlspecialchars($_SESSION['cliente_nombre']) ?></span>
					<hr>
					<form method="POST">
						<button type="submit" name="logout" class="btn btn-danger btn-sm w-100"><i class="fas fa-door-open fa-fw"></i> &nbsp; Cerrar sesión</button>
					</form>
				</div>
			<?php else: ?>
				<i class="fas fa-user-alt" onclick="show_popup_login()" title="Login"></i>
				<div class="div-bordered popup-login">
					<span class="text-center poppins-regular font-weight-bold">Inicio de sesión</span>
					<form class="full-box" method="POST" action="login.php">
						<div class="form-outline mb-3">
							<input type="email" class="form-control" name="login_email_cliente" maxlength="70" required placeholder="Email">
						</div>
						<div class="form-outline mb-3">
							<input type="password" class="form-control" name="login_clave_cliente" maxlength="100" required placeholder="Contraseña">
						</div>
						<p class="text-center">
							<button class="btn btn-info btn-sm w-100">Iniciar sesión</button>
						</p>
					</form>
					<hr>
					<p class="text-center full-box">¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
				</div>
			<?php endif; ?>
		</div>

		<!-- Carrito -->
		<a href="carrito.php" class="header-button full-box text-center" title="Carrito">
			<i class="fas fa-shopping-bag"></i>
			<span class="badge bg-warning rounded-pill bag-count">
				<?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0; ?>
			</span>
		</a>
	</div>
</header>

<?php
// Lógica de logout
if (isset($_POST['logout'])) {
	session_destroy();
	echo "<script>window.location.href = window.location.href;</script>";
	exit;
}
?>

<!-- Formulario -->
<div class="container container-web-page">
	<h3 class="font-weight-bold text-uppercase">Crear cuenta</h3>
	<p>Complete todos los campos correctamente.</p>
	<hr>
	<form method="POST" action="registro.php" class="div-bordered" style="padding: 15px;">
		<fieldset>
			<legend><i class="far fa-address-card"></i> Información personal</legend>
			<div class="row">
				<div class="col-md-6 mb-3">
					<select class="form-control" name="cliente_tipo_documento_reg" required>
						<option value="">Tipo de documento</option>
						<option value="DNI">DNI</option>
						<option value="CE">CE</option>
						<option value="Pasaporte">Pasaporte</option>
					</select>
				</div>
				<div class="col-md-6 mb-3">
					<input type="text" class="form-control" name="cliente_numero_documento_reg" placeholder="Número de documento" maxlength="30" required>
				</div>
				<div class="col-md-6 mb-3">
					<input type="text" class="form-control" name="cliente_nombre_reg" placeholder="Nombres" maxlength="35" required>
				</div>
				<div class="col-md-6 mb-3">
					<input type="text" class="form-control" name="cliente_apellido_reg" placeholder="Apellidos" maxlength="35" required>
				</div>
				<div class="col-md-6 mb-3">
					<input type="text" class="form-control" name="cliente_telefono_reg" placeholder="Teléfono" maxlength="20" required>
				</div>
			</div>
		</fieldset>

		<fieldset class="mt-4">
			<legend><i class="fas fa-map-marked-alt"></i> Dirección</legend>
			<div class="row">
				<div class="col-md-4 mb-3">
					<select class="form-control" name="cliente_provincia_reg" required>
						<option value="">Departamento</option>
						<option value="Lima">Lima</option>
						<option value="Ica">Ica</option>
						<option value="Arequipa">Arequipa</option>
						<option value="Cusco">Cusco</option>
						<!-- agrega más si deseas -->
					</select>
				</div>
				<div class="col-md-4 mb-3">
					<input type="text" class="form-control" name="cliente_ciudad_reg" placeholder="Ciudad" required>
				</div>
				<div class="col-md-4 mb-3">
					<input type="text" class="form-control" name="cliente_direccion_reg" placeholder="Dirección" maxlength="70" required>
				</div>
			</div>
		</fieldset>

		<fieldset class="mt-4">
			<legend><i class="fas fa-user-lock"></i> Cuenta</legend>
			<div class="row">
				<div class="col-md-4 mb-3">
					<input type="email" class="form-control" name="cliente_email_reg" placeholder="Email" required>
				</div>
				<div class="col-md-4 mb-3">
					<input type="password" class="form-control" name="cliente_clave_1_reg" placeholder="Contraseña" required>
				</div>
				<div class="col-md-4 mb-3">
					<input type="password" class="form-control" name="cliente_clave_2_reg" placeholder="Repetir contraseña" required>
				</div>
			</div>
		</fieldset>

		<div class="text-center mt-4">
			<button type="submit" class="btn btn-info btn-sm"><i class="far fa-paper-plane"></i> Crear cuenta</button>
		</div>
	</form>
</div>

<!-- Footer -->
<footer class="footer mt-5">
	<div class="container text-center">
		<p>© La Casa del Sazón. Todos los derechos reservados.</p>
	</div>
</footer>

<!-- Scripts -->
<script src="./js/mdb.min.js"></script>

<?php if (isset($mensaje)): ?>
<script>
Swal.fire({
	title: "<?= $mensaje['titulo'] ?>",
	text: "<?= $mensaje['texto'] ?>",
	icon: "<?= $mensaje['tipo'] ?>",
	confirmButtonText: "Aceptar"
}).then(() => {
	<?php if (isset($mensaje['redirigir'])): ?>
	window.location = "<?= $mensaje['redirigir'] ?>";
	<?php endif; ?>
});
</script>
<?php endif; ?>
</body>
</html>
