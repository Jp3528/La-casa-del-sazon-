<?php
session_start();
include("conexion.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Restaurant</title>

	<link rel="stylesheet" href="./css/normalize.css">
	<link rel="stylesheet" href="./css/mdb.min.css">
	<link rel="stylesheet" href="./css/all.css">
	<script src="./js/sweetalert2.js"></script>
	<link rel="stylesheet" href="./css/style.css">
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
				<li><a href="mispedidos.php">Mis pedidos</a></li>
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

<?php
// Lógica de logout
if (isset($_POST['logout'])) {
	session_destroy();
	echo "<script>window.location = window.location.href;</script>";
	exit;
}
?>

<!-- Banner -->
<div class="banner">
	<div class="banner-body">
		<h3 class="text-uppercase">Bienvenido al RESTAURANTE La Casa del Sazón</h3>
		<p>Los mejores platillos y la mejor calidad los encuentras aquí</p>
		<a href="menu.php" class="btn btn-warning"><i class="fas fa-hamburger fa-fw"></i> &nbsp; Ir al menú</a>
	</div>
</div>

<!-- Servicios -->
<div class="container container-web-page">
	<h3 class="text-center text-uppercase poppins-regular font-weight-bold">Nuestros servicios</h3>
	<br>
	<div class="row">
		<div class="col-12 col-sm-6 col-md-4 text-center">
			<i class="fas fa-shipping-fast fa-5x"></i>
			<h5 class="text-uppercase font-weight-bold">Envíos a domicilio</h5>
			<p>Haz tu pedido al 999 999 999. Envío gratuito a domicilio.</p>
		</div>
		<div class="col-12 col-sm-6 col-md-4 text-center">
			<i class="fas fa-utensils fa-5x"></i>
			<h5 class="text-uppercase font-weight-bold">Ventas al por mayor</h5>
			<p>A más pedidos, mayores descuentos.</p>
		</div>
		<div class="col-12 col-sm-6 col-md-4 text-center">
			<i class="fas fa-store-alt fa-5x"></i>
			<h5 class="text-uppercase font-weight-bold">Reservaciones</h5>
			<p>Reserva tu mesa al 999 999 999.</p>
		</div>
	</div>
</div>

<hr>

<!-- Productos -->
<div class="container-fluid container-web-page">
	<h3 class="text-center text-uppercase poppins-regular font-weight-bold">Platillos destacados</h3>
	<div class="container-cards full-box">
<?php
$sql = "SELECT * FROM productos WHERE stock > 0 LIMIT 3";
$resultado = $conn->query($sql);
if ($resultado->num_rows > 0) {
	while ($row = $resultado->fetch_assoc()) {
		echo '<div class="card shadow-1-strong" style="height: 100%; min-height: 430px;">';
		echo '  <img class="card-img-top" src="./assets/platillos/' . htmlspecialchars($row["imagen"]) . '" alt="' . htmlspecialchars($row["nombre"]) . '" style="height: 200px; object-fit: cover;">';
		echo '  <div class="card-body text-center">';
		echo '    <h5 class="card-title font-weight-bold">' . htmlspecialchars($row["nombre"]) . '</h5>';
		echo '    <p class="card-text text-muted small text-justify" style="min-height: 60px;">' . htmlspecialchars($row["descripcion"]) . '</p>';
		echo '    <p class="card-text lead"><span class="badge bg-secondary">S/. ' . number_format($row["precio"], 2) . '</span></p>';
		echo '  </div>';
		echo '  <div class="card-body text-center">';
		echo '    <form method="POST" action="carrito.php">';
		echo '      <input type="hidden" name="producto_id" value="' . $row["id"] . '">';
		echo '      <input type="hidden" name="nombre" value="' . htmlspecialchars($row["nombre"]) . '">';
		echo '      <input type="hidden" name="precio" value="' . $row["precio"] . '">';
		echo '      <button type="submit" class="btn btn-success btn-sm">';
		echo '        <i class="fas fa-shopping-bag fa-fw"></i> &nbsp; Agregar';
		echo '      </button>';
		echo '    </form>';
		echo '  </div>';
		echo '</div>';
	}
} else {
	echo "<p class='text-center'>No hay productos disponibles.</p>";
}
?>
	</div>
</div>

<hr>

<!-- Registro -->
<div class="container container-web-page">
	<div class="row justify-content-md-center">
		<div class="col-12 col-md-6">
			<figure class="full-box">
				<img src="./assets/img/registration.png" alt="Registro" class="img-fluid">
			</figure>
		</div>
		<div class="w-100"></div>
		<div class="col-12 col-md-6 text-center">
			<h3 class="text-uppercase font-weight-bold">Crea tu cuenta</h3>
			<p>Regístrate para hacer tus pedidos desde casa fácilmente.</p>
			<a href="registro.php" class="btn btn-primary">Crear cuenta</a>
		</div>
	</div>
</div>

<!-- Footer -->
<footer class="footer">
	<div class="container">
		<div class="row">
			<div class="col-12 col-md-4">
				<ul class="list-unstyled">
					<li><h5 class="font-weight-bold"><i class="far fa-copyright"></i> La Casa del Sazón </h5></li>
					<li>Todos los derechos reservados</li>
				</ul>
			</div>
			<div class="col-12 col-md-4">
				<ul class="list-unstyled">
					<li><h5 class="font-weight-bold">Perú</h5></li>
					<li><i class="fas fa-map-marker-alt fa-fw"></i> Ica, Av. Las flores N° 215</li>
				</ul>
			</div>
			<div class="col-12 col-md-4">
				<ul class="list-unstyled">
					<li><h5 class="font-weight-bold">Síguenos:</h5></li>
					<li><a href="#" class="footer-link"><i class="fab fa-facebook fa-fw"></i> Facebook</a></li>
					<li><a href="#" class="footer-link"><i class="fab fa-youtube fa-fw"></i> YouTube</a></li>
				</ul>
			</div>
		</div>
	</div>
</footer>

<!-- Scripts -->
<script src="./js/mdb.min.js"></script>
<script src="./js/main.js"></script>

<?php if (isset($_GET['login_error']) && $_GET['login_error'] == 1): ?>
<script>
Swal.fire({
	title: "Debes iniciar sesión",
	text: "Para realizar tu pedido, inicia sesión con tu cuenta.",
	icon: "warning",
	confirmButtonText: "Entendido"
});
</script>
<?php endif; ?>

</body>
</html>
