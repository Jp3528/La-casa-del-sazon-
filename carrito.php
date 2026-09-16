<?php
session_start();
include("conexion.php");

// Inicializar carrito si no existe
if (!isset($_SESSION["carrito"])) $_SESSION["carrito"] = [];

// Agregar producto
if (
    $_SERVER["REQUEST_METHOD"] == "POST" &&
    !empty($_POST["producto_id"]) &&
    !empty($_POST["nombre"]) &&
    isset($_POST["precio"])
) {
    $id = $_POST["producto_id"];
    $nombre = htmlspecialchars($_POST["nombre"]);
    $precio = floatval($_POST["precio"]);

    if (isset($_SESSION["carrito"][$id])) {
        $_SESSION["carrito"][$id]["cantidad"]++;
    } else {
        $_SESSION["carrito"][$id] = [
            "nombre" => $nombre,
            "precio" => $precio,
            "cantidad" => 1
        ];
    }
}

// Operaciones del carrito
if (isset($_GET["aumentar"]) && isset($_SESSION["carrito"][$_GET["aumentar"]])) {
    $_SESSION["carrito"][$_GET["aumentar"]]["cantidad"]++;
}

if (isset($_GET["reducir"]) && isset($_SESSION["carrito"][$_GET["reducir"]])) {
    if ($_SESSION["carrito"][$_GET["reducir"]]["cantidad"] > 1) {
        $_SESSION["carrito"][$_GET["reducir"]]["cantidad"]--;
    } else {
        unset($_SESSION["carrito"][$_GET["reducir"]]);
    }
}

if (isset($_GET["eliminar"])) {
    unset($_SESSION["carrito"][$_GET["eliminar"]]);
}

if (isset($_GET["vaciar"])) {
    $_SESSION["carrito"] = [];
}

// Logout
if (isset($_POST['logout'])) {
    session_destroy();
    echo "<script>window.location.href = 'carrito.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/normalize.css">
    <link rel="stylesheet" href="./css/mdb.min.css">
    <link rel="stylesheet" href="./css/all.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./estilo1.css">
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
<div class="container container-web-page mt-5">
    <h3 class="text-center">🛒 Carrito de Compras</h3>
    <hr>

    <?php if (!empty($_SESSION["carrito"])): ?>
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total = 0; ?>
                    <?php foreach ($_SESSION["carrito"] as $id => $item): ?>
                        <?php
                        if (!isset($item["nombre"], $item["precio"], $item["cantidad"])) continue;
                        $subtotal = $item["precio"] * $item["cantidad"];
                        $total += $subtotal;
                        ?>
                        <tr>
                            <td><?= $item["nombre"] ?></td>
                            <td>S/. <?= number_format($item["precio"], 2) ?></td>
                            <td>
                                <a href="?reducir=<?= $id ?>" class="btn btn-sm btn-warning">–</a>
                                <?= $item["cantidad"] ?>
                                <a href="?aumentar=<?= $id ?>" class="btn btn-sm btn-info">+</a>
                            </td>
                            <td>S/. <?= number_format($subtotal, 2) ?></td>
                            <td><a href="?eliminar=<?= $id ?>" class="btn btn-danger btn-sm">Eliminar</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h5 class="text-end">Total: <strong>S/. <?= number_format($total, 2) ?></strong></h5>
        <div class="text-end">
            <a href="?vaciar=1" class="btn btn-warning">Vaciar Carrito</a>
            <a href="realizarpedido.php" class="btn btn-success">Pagar ahora</a>
        </div>
    <?php else: ?>
        <p class="text-center">🛍️ Tu carrito está vacío.</p>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="footer mt-5">
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-4">
                <ul class="list-unstyled">
                    <li><h5 class="font-weight-bold"><i class="far fa-copyright"></i> La Casa del Sazón</h5></li>
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
<script>
function show_popup_login() {
	const popup = document.querySelector('.popup-login');
	if (popup) popup.classList.toggle('active');
}

document.addEventListener('click', function (e) {
	const popup = document.querySelector('.popup-login');
	const icon = document.querySelector('.btn-login-menu i');
	if (!popup || !icon) return;
	if (!popup.contains(e.target) && !icon.contains(e.target)) {
		popup.classList.remove('active');
	}
});
</script>
</body>
</html>
