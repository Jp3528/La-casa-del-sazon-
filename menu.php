<?php 
session_start();
include("conexion.php");

// Mostrar mensaje si hubo error de login
$mensaje = "";
if (isset($_GET["login_error"])) {
    $mensaje = "<script>
        Swal.fire({
            icon: 'error',
            title: 'Error de inicio de sesión',
            text: 'Correo o contraseña incorrectos'
        });
    </script>";
}

// Logout
if (isset($_POST['logout'])) {
    session_destroy();
    echo "<script>window.location = 'menu.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Restaurant - Menú</title>

	<!-- Estilos -->
	<link rel="stylesheet" href="./css/normalize.css">
	<link rel="stylesheet" href="./css/mdb.min.css">
	<link rel="stylesheet" href="./css/all.css">
	<link rel="stylesheet" href="./css/style.css">
	<link rel="stylesheet" href="./estilo1.css">
	<script src="./js/sweetalert2.js"></script>

	<style>
		#busqueda-container {
			max-width: 300px;
			margin-left: auto;
			margin-bottom: 20px;
			display: flex;
			align-items: center;
		}
		#buscador {
			flex-grow: 1;
			margin-right: 5px;
		}
		.popup-login {
			display: none;
			position: absolute;
			right: 0;
			top: 100%;
			background: #fff;
			border: 1px solid #ccc;
			padding: 15px;
			box-shadow: 0 0 10px rgba(0,0,0,0.1);
			z-index: 1000;
			width: 250px;
		}
		.popup-login.active {
			display: block;
		}
	</style>
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

<!-- Contenido -->
<div class="container container-web-page mt-4">
	<h3 class="font-weight-bold poppins-regular text-uppercase text-center">Menú de platillos</h3>
	<p class="text-center mb-4">Bienvenido al menú de platillos, acá encontrará todos los platillos disponibles en el restaurante. Puede ordenar los platillos según su preferencia.</p>

	<!-- Buscador -->
	<div id="busqueda-container" class="mb-4">
		<input type="text" id="buscador" class="form-control" placeholder="Buscar por nombre...">
		<i class="fas fa-search text-muted"></i>
	</div>

	<!-- Productos -->
	<div class="container-cards full-box" id="contenedor-productos">
		<?php
		$sql = "SELECT * FROM productos WHERE stock > 0";
		$result = $conn->query($sql);

		if ($result->num_rows > 0):
			while ($row = $result->fetch_assoc()):
		?>
		<div class="card shadow-1-strong mb-4" data-nombre="<?= htmlspecialchars($row['nombre']) ?>">
			<img class="card-img-top" src="./assets/platillos/<?= htmlspecialchars($row["imagen"]) ?>" alt="<?= htmlspecialchars($row["nombre"]) ?>">
			<div class="card-body text-center">
				<h5 class="card-title font-weight-bold"><?= htmlspecialchars($row["nombre"]) ?></h5>
				<p class="card-text small"><?= htmlspecialchars($row["descripcion"]) ?></p>
				<p class="lead mb-2"><span class="badge bg-secondary">S/. <?= number_format($row["precio"], 2) ?></span></p>
				<form method="POST" action="carrito.php" class="d-inline">
					<input type="hidden" name="producto_id" value="<?= $row["id"] ?>">
					<input type="hidden" name="nombre" value="<?= htmlspecialchars($row["nombre"], ENT_QUOTES, 'UTF-8') ?>">
					<input type="hidden" name="precio" value="<?= $row["precio"] ?>">
					<button type="submit" class="btn btn-success btn-sm">
						<i class="fas fa-shopping-bag fa-fw"></i> &nbsp; Agregar
					</button>
				</form>
			</div>
		</div>
		<?php endwhile; else: ?>
			<p class="text-center">No hay productos disponibles en este momento.</p>
		<?php endif; ?>
	</div>
</div>

<!-- Footer -->
<footer class="footer mt-5">
	<div class="container">
		<div class="row">
			<div class="col-12 col-md-4">
				<ul class="list-unstyled">
					<li><h5 class="font-weight-bold"><i class="far fa-copyright"></i>La Casa del Sazón</h5></li>
					<li>Todos los derechos reservados</li>
				</ul>
			</div>
			<div class="col-12 col-md-4">
				<ul class="list-unstyled">
					<li><h5 class="font-weight-bold">Perú</h5></li>
					<li><i class="fas fa-map-marker-alt fa-fw"></i> Perú, Ica, Av. Las flores N° 215</li>
				</ul>
			</div>
			<div class="col-12 col-md-4">
				<ul class="list-unstyled">
					<li><h5 class="font-weight-bold">Síguenos en:</h5></li>
					<li><a href="https://facebook.com" class="footer-link" target="_blank"><i class="fab fa-facebook fa-fw"></i> Facebook</a></li>
					<li><a href="https://youtube.com" class="footer-link" target="_blank"><i class="fab fa-youtube fa-fw"></i> YouTube</a></li>
				</ul>
			</div>
		</div>
	</div>
</footer>

<!-- Scripts -->
<script src="./js/mdb.min.js"></script>
<script>
function show_popup_login() {
	const popup = document.querySelector('.popup-login');
	if (popup) popup.classList.toggle('active');
}

document.addEventListener('click', function (e) {
	const icon = document.querySelector('.btn-login-menu i');
	const popup = document.querySelector('.popup-login');
	if (!popup || !icon) return;

	if (!popup.contains(e.target) && !icon.contains(e.target)) {
		popup.classList.remove('active');
	}
});

document.getElementById("buscador").addEventListener("input", function () {
	const termino = this.value.toLowerCase();
	const productos = document.querySelectorAll("#contenedor-productos .card");

	productos.forEach(card => {
		const nombre = card.dataset.nombre.toLowerCase();
		card.style.display = nombre.includes(termino) ? "block" : "none";
	});
});
</script>

<?= $mensaje ?>
</body>
</html>
