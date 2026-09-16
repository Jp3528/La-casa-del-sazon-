<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["cliente_id"])) {
    header("Location: login.php");
    exit();
}

$cliente_id = $_SESSION["cliente_id"];

// Obtener dirección del cliente
$sql_cliente = "SELECT direccion FROM clientes WHERE id = ?";
$stmt = $conn->prepare($sql_cliente);
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$stmt->bind_result($direccion_cliente);
$stmt->fetch();
$stmt->close();

// Procesar el pedido
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["direccion_envio"], $_POST["metodo_pago"])) {
    $direccion_envio = trim($_POST["direccion_envio"]);
    $metodo_pago_id = intval($_POST["metodo_pago"]);
    $total = 0;

    if (!isset($_SESSION["carrito"]) || count($_SESSION["carrito"]) === 0) {
        echo "<script>alert('Tu carrito está vacío.'); window.location='menu.php';</script>";
        exit();
    }

    foreach ($_SESSION["carrito"] as $item) {
        $total += $item["precio"] * $item["cantidad"];
    }

    $stmt = $conn->prepare("INSERT INTO pedidos (cliente_id, direccion_envio, total, estado, metodo_pago_id) VALUES (?, ?, ?, 'Pendiente', ?)");
    $stmt->bind_param("isdi", $cliente_id, $direccion_envio, $total, $metodo_pago_id);

    if ($stmt->execute()) {
        $pedido_id = $stmt->insert_id;
        $stmt->close();

        $stmt_detalle = $conn->prepare("INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad) VALUES (?, ?, ?)");
        foreach ($_SESSION["carrito"] as $producto_id => $item) {
            $stmt_detalle->bind_param("iii", $pedido_id, $producto_id, $item["cantidad"]);
            $stmt_detalle->execute();
        }
        $stmt_detalle->close();

        $_SESSION["carrito"] = [];

        echo "<script>alert('¡Pedido realizado con éxito!'); window.location='menu.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error al registrar el pedido.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Realizar Pedido</title>
    <link rel="stylesheet" href="./css/mdb.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/all.css">
    <link rel="stylesheet" href="./estilo1.css">

    <script src="./js/mdb.min.js"></script>
    <style>
        .pago-extra {
            display: none;
            margin-top: 20px;
        }
        .qr-image {
            width: 150px;
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
            </ul>
        </nav>
        <a href="carrito.php" class="header-button full-box text-center" title="Carrito">
            <i class="fas fa-shopping-bag"></i>
            <span class="badge bg-warning rounded-pill bag-count">
                <?= isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0 ?>
            </span>
        </a>
    </div>
</header>

<div class="container mt-5">
    <h3 class="text-center">Resumen del Pedido</h3>
    <table class="table table-bordered">
        <thead class="table-dark">
        <tr>
            <th>Producto</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
        </tr>
        </thead>
        <tbody>
        <?php $total = 0;
        foreach ($_SESSION["carrito"] as $item): 
            $subtotal = $item["precio"] * $item["cantidad"];
            $total += $subtotal; ?>
            <tr>
                <td><?= htmlspecialchars($item["nombre"]) ?></td>
                <td>S/. <?= number_format($item["precio"], 2) ?></td>
                <td><?= $item["cantidad"] ?></td>
                <td>S/. <?= number_format($subtotal, 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <h5 class="text-end">Total: <strong>S/. <?= number_format($total, 2) ?></strong></h5>

    <form method="POST" enctype="multipart/form-data" class="mt-4">
        <div class="mb-3">
            <label class="form-label">Dirección de envío</label>
            <textarea name="direccion_envio" class="form-control" required><?= htmlspecialchars($direccion_cliente) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Método de pago</label>
            <select name="metodo_pago" id="metodo_pago" class="form-control" required onchange="mostrarOpcionesPago()">
                <option value="">Seleccione un método</option>
                <?php
                $metodos = $conn->query("SELECT id, nombre_metodo FROM metodos_pago");
                while ($row = $metodos->fetch_assoc()) {
                    echo "<option value='{$row["id"]}' data-nombre='" . strtolower($row["nombre_metodo"]) . "'>" . $row["nombre_metodo"] . "</option>";
                }
                ?>
            </select>
        </div>

        <!-- Pago Yape / Plin -->
        <div id="yape-plin" class="pago-extra">
            <p>Escanee el código QR o use el número <strong>999999999</strong></p>
            <img src="assets/img/yapeplin.png" alt="QR" class="qr-image mb-2">
            <div class="form-group">
                <label>Adjuntar comprobante</label>
                <input type="file" name="comprobante" class="form-control">
            </div>
        </div>

        <!-- Pago Tarjeta -->
        <div id="tarjeta" class="pago-extra">
            <div class="mb-3">
                <label>Número de tarjeta</label>
                <input type="text" class="form-control" placeholder="XXXX-XXXX-XXXX-XXXX">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Fecha de vencimiento</label>
                    <input type="text" class="form-control" placeholder="MM/AA">
                </div>
                <div class="col-md-6 mb-3">
                    <label>CVV</label>
                    <input type="text" class="form-control" placeholder="123">
                </div>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-success">Confirmar Pedido</button>
        </div>
    </form>
</div>

<!-- Footer -->
<footer class="footer mt-5">
    <div class="container text-center">
        <p>© La Casa del Sazón. Todos los derechos reservados.</p>
    </div>
</footer>

<script>
function mostrarOpcionesPago() {
    const select = document.getElementById('metodo_pago');
    const selectedOption = select.options[select.selectedIndex];
    const metodo = selectedOption.getAttribute('data-nombre');

    document.getElementById('yape-plin').style.display = 'none';
    document.getElementById('tarjeta').style.display = 'none';

    if (metodo === 'yape' || metodo === 'plin') {
        document.getElementById('yape-plin').style.display = 'block';
    } else if (metodo === 'tarjeta de débito' || metodo === 'tarjeta de crédito') {
        document.getElementById('tarjeta').style.display = 'block';
    }
}
</script>
</body>
</html>
