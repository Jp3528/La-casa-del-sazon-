<?php
session_start();

include __DIR__ . "/conexion.php"; 

// Mostrar errores de PHP (solo en desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = 1; 
}

$mensaje = "";

if (!db_disponible()) {
    die("<div class='container mt-5 alert alert-danger'>Error: No se pudo conectar a la base de datos. Configura DB_HOST, DB_USER, DB_PASSWORD y DB_NAME en Vercel.</div>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $pedido_id = intval($_POST['pedido_id']);

    if ($_POST['action'] === 'actualizar_estado') {
        $nuevo_estado = $_POST['estado'];
        $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevo_estado, $pedido_id);
        if ($stmt->execute()) {
            $mensaje = "<script>Swal.fire('Éxito', 'Estado del pedido actualizado.', 'success');</script>";
        } else {
            $mensaje = "<script>Swal.fire('Error', 'No se pudo actualizar el estado.', 'error');</script>";
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'asignar_repartidor') {
        $repartidor_id = intval($_POST['repartidor_id']);
        $stmt = $conn->prepare("UPDATE pedidos SET repartidor_id = ?, estado = 'Enviado' WHERE id = ?");
        $stmt->bind_param("ii", $repartidor_id, $pedido_id);
        if ($stmt->execute()) {
            $mensaje = "<script>Swal.fire('Éxito', 'Repartidor asignado y pedido marcado como Enviado.', 'success');</script>";
        } else {
            $mensaje = "<script>Swal.fire('Error', 'No se pudo asignar el repartidor.', 'error');</script>";
        }
        $stmt->close();
    }
}

// Obtener pedidos para mostrar en la tabla
$pedidos_query = "
    SELECT p.id, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido, 
           p.fecha, p.total, p.estado, p.direccion_envio, mp.nombre_metodo, 
           r.nombre AS repartidor_nombre, p.repartidor_id
    FROM pedidos p
    JOIN clientes c ON p.cliente_id = c.id
    JOIN metodos_pago mp ON p.metodo_pago_id = mp.id
    LEFT JOIN repartidores r ON p.repartidor_id = r.id
    ORDER BY p.fecha DESC
";

$pedidos_result = $conn->query($pedidos_query);

// Obtener repartidores para el select
$repartidores_query = "SELECT id, nombre FROM repartidores WHERE estado_disponibilidad IN ('disponible', 'ocupado') ORDER BY nombre ASC";
$repartidores_result = $conn->query($repartidores_query);
$repartidores = [];
if ($repartidores_result) { // Verifica si la consulta fue exitosa
    while ($row = $repartidores_result->fetch_assoc()) {
        $repartidores[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Pedidos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/mdb.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/all.css">
    <script src="./js/sweetalert2.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .container { background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .table th, .table td { vertical-align: middle; }
        .badge { font-size: 0.85em; padding: 0.5em 0.7em; }
        .d-flex.flex-column { gap: 10px; } 
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">Panel de Administración de Pedidos</h2>

    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID Pedido</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Repartidor Asignado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($pedidos_result && $pedidos_result->num_rows > 0): ?>
                <?php while ($pedido = $pedidos_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($pedido['id']) ?></td>
                    <td><?= htmlspecialchars($pedido['cliente_nombre'] . ' ' . $pedido['cliente_apellido']) ?></td>
                    <td><?= htmlspecialchars($pedido['fecha'] ?? 'Fecha no disponible') ?></td>
                    <td>S/. <?= number_format($pedido['total'], 2) ?></td>
                    <td>
                        <span class="badge 
                            <?php 
                            if ($pedido['estado'] == 'Pendiente') echo 'bg-info';
                            elseif ($pedido['estado'] == 'Enviado') echo 'bg-warning text-dark'; 
                            elseif ($pedido['estado'] == 'Entregado') echo 'bg-success';
                            elseif ($pedido['estado'] == 'Cancelado') echo 'bg-danger'; 
                            else echo 'bg-secondary';
                            ?>">
                            <?= htmlspecialchars($pedido['estado']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($pedido['repartidor_nombre'] ?: 'No asignado') ?></td>
                    <td>
                        <div class="d-flex flex-column">
                            <form action="" method="POST" class="mb-2">
                                <input type="hidden" name="action" value="actualizar_estado">
                                <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">
                                <select name="estado" class="form-select form-select-sm mb-1">
                                    <option value="Pendiente" <?= ($pedido['estado'] == 'Pendiente') ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="Enviado" <?= ($pedido['estado'] == 'Enviado') ? 'selected' : '' ?>>Enviado</option>
                                    <option value="Entregado" <?= ($pedido['estado'] == 'Entregado') ? 'selected' : '' ?>>Entregado</option>
                                    <option value="Cancelado" <?= ($pedido['estado'] == 'Cancelado') ? 'selected' : '' ?>>Cancelado</option>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm w-100">Actualizar Estado</button>
                            </form>

                            <?php if ($pedido['estado'] === 'Pendiente' || ($pedido['repartidor_id'] ?? null) === null): ?>
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="asignar_repartidor">
                                <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">
                                <select name="repartidor_id" class="form-select form-select-sm mb-1" required>
                                    <option value="">Asignar Repartidor</option>
                                    <?php foreach ($repartidores as $rep): ?>
                                        <option value="<?= $rep['id'] ?>" <?= (($pedido['repartidor_id'] ?? null) == $rep['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($rep['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-warning btn-sm w-100">Asignar</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No hay pedidos registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="./js/mdb.min.js"></script>
<?= $mensaje ?>

</body>
</html>
