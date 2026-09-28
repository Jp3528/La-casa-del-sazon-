<?php
session_start();
include("conexion.php");

// Mostrar errores de PHP (solo en desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// *** Lógica de autenticación del cliente ***
if (!isset($_SESSION["cliente_id"])) {
    header("Location: login.php");
    exit();
}

$cliente_id = $_SESSION["cliente_id"];

// Lógica para cerrar sesión
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

// Obtener los pedidos del cliente

$pedidos_query = "
    SELECT 
        p.id, 
        p.fecha, 
        p.total, 
        p.estado, 
        p.direccion_envio, 
        mp.nombre_metodo,
        p.repartidor_id, -- Incluimos el ID del repartidor
        r.nombre AS repartidor_nombre -- Incluimos el nombre del repartidor
    FROM pedidos p 
    JOIN metodos_pago mp ON p.metodo_pago_id = mp.id 
    LEFT JOIN repartidores r ON p.repartidor_id = r.id -- LEFT JOIN para que funcione si no hay repartidor asignado
    WHERE p.cliente_id = ? 
    ORDER BY p.fecha DESC
";

// Usar prepared statement para la consulta principal de pedidos
if (!db_disponible()) {
    die("<div class='container mt-5 alert alert-danger'>Error: No se pudo conectar a la base de datos. Configura DB_HOST, DB_USER, DB_PASSWORD y DB_NAME en Vercel.</div>");
}
$stmt_pedidos = $conn->prepare($pedidos_query);
if ($stmt_pedidos === false) {
    die("Error al preparar la consulta de pedidos: " . $conn->error);
}
$stmt_pedidos->bind_param("i", $cliente_id);
$stmt_pedidos->execute();
$pedidos_result = $stmt_pedidos->get_result();
$pedidos_data = $pedidos_result->fetch_all(MYSQLI_ASSOC); 
$stmt_pedidos->close();


function getEstadoProgreso($estado) {
    switch (strtolower($estado)) {
        case 'pendiente': return ['33%', 'Pendiente', 'bg-info'];
        case 'enviado':   return ['66%', 'Enviado', 'bg-warning text-dark']; 
        case 'entregado': return ['100%', 'Entregado', 'bg-success'];
        case 'cancelado': return ['0%', 'Cancelado', 'bg-danger']; 
        default: return ['0%', $estado, 'bg-secondary'];
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Pedidos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/mdb.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/all.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="./js/sweetalert2.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .pedido-card {
            border: 1px solid #ccc;
            border-radius: 10px;
            margin-bottom: 30px;
            padding: 20px;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
        }
        .estado-etiqueta {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            
        }
        .modal-content {
            border-radius: 10px;
        }
        .progress {
            height: 20px;
        }
        #map {
            height: 400px; 
            width: 100%;  
            border-radius: 8px;
            margin-top: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .delivery-marker {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            color: #fff;
            background: #198754;
            border: 3px solid #fff;
            box-shadow: 0 8px 20px rgba(25, 135, 84, 0.35);
            font-size: 18px;
        }
        .whatsapp-float {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            text-align: right;
        }
        .whatsapp-float .mensaje {
            background-color: #25D366;
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .whatsapp-float a {
            display: inline-block;
            background-color: #25D366;
            color: white;
            padding: 10px 12px;
            border-radius: 50%;
            font-size: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            text-decoration: none;
        }
        .whatsapp-float a:hover {
            background-color: #20b058;
        }
    </style>
</head>
<body>
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

            <div class="header-button full-box text-center btn-login-menu">
                <?php if (isset($_SESSION['cliente_nombre'])): ?>
                    <i class="fas fa-user-circle" onclick="show_popup_login()" title="Mi cuenta"></i>
                    <div class="div-bordered popup-login">
                        <span class="text-center poppins-regular font-weight-bold">Hola, <?= htmlspecialchars($_SESSION['cliente_nombre']) ?></span>
                        <hr>
                        <form method="POST">
                            <button type="submit" name="logout" class="btn btn-danger btn-sm w-100"><i class="fas fa-door-open fa-fw"></i>   Cerrar sesión</button>
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

            <a href="carrito.php" class="header-button full-box text-center" title="Carrito">
                <i class="fas fa-shopping-bag"></i>
                <span class="badge bg-warning rounded-pill bag-count">
                    <?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0; ?>
                </span>
            </a>
        </div>
    </header>

    <div class="container mt-5">
        <h3 class="text-center mb-4"><i class="fas fa-box-open"></i> Mis Pedidos</h3>

        <?php if (empty($pedidos_data)): ?>
            <div class="alert alert-info text-center" role="alert">
                No tienes pedidos realizados.
            </div>
        <?php else: ?>
            <?php foreach ($pedidos_data as $pedido): 
                list($porcentaje, $estadoTexto, $colorClase) = getEstadoProgreso($pedido['estado']);
            ?>
            <div class="pedido-card">
                <div class="row align-items-center">
                    <div class="col-md-2"><strong>#<?= htmlspecialchars($pedido['id']) ?></strong></div>
                    <div class="col-md-3"><?= htmlspecialchars($pedido['fecha'] ?? 'Fecha no disponible') ?></div>
                    <div class="col-md-2">S/. <?= number_format($pedido['total'], 2) ?></div>
                    <div class="col-md-2"><span class="estado-etiqueta <?= $colorClase ?>"><?= htmlspecialchars($estadoTexto) ?></span></div>
                    <div class="col-md-3 text-end">
                        <button class="btn btn-info btn-sm" data-mdb-toggle="modal" data-mdb-target="#modal<?= $pedido['id'] ?>">Ver Detalles</button>
                        
                        <?php if ($pedido['estado'] == 'Enviado' && !empty($pedido['repartidor_id'])): ?>
                            <button class="btn btn-success btn-sm mt-2 mt-md-0" 
                                    onclick="showMap(<?= $pedido['id'] ?>, <?= $pedido['repartidor_id'] ?>, '<?= htmlspecialchars($pedido['repartidor_nombre'] ?: 'No asignado') ?>')">
                                <i class="fas fa-map-marker-alt"></i> Rastrear Repartidor
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modal<?= $pedido['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $pedido['id'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content p-4">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-receipt"></i> Pedido #<?= htmlspecialchars($pedido['id']) ?> - <?= htmlspecialchars($pedido['fecha'] ?? 'Fecha no disponible') ?></h5>
                            <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong>Estado:</strong> <?= htmlspecialchars($estadoTexto) ?></p>
                            <div class="progress mb-3">
                                <div class="progress-bar <?= $colorClase ?>" role="progressbar" style="width: <?= $porcentaje ?>;">
                                    <?= htmlspecialchars($porcentaje) ?>
                                </div>
                            </div>
                            <p><strong>Repartidor Asignado:</strong> <?= htmlspecialchars($pedido['repartidor_nombre'] ?: 'No asignado') ?></p>
                            <p><strong>Dirección de envío:</strong> <?= htmlspecialchars($pedido['direccion_envio']) ?></p>
                            <p><strong>Método de pago:</strong> <?= htmlspecialchars($pedido['nombre_metodo']) ?></p>
                            <p><strong>Total:</strong> S/. <?= number_format($pedido['total'], 2) ?></p>

                            <hr>
                            <h6>Productos:</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                
                                $stmt_detalles = $conn->prepare("
                                    SELECT dp.cantidad, pr.nombre 
                                    FROM detalle_pedido dp 
                                    JOIN productos pr ON pr.id = dp.producto_id 
                                    WHERE dp.pedido_id = ?
                                ");
                                if ($stmt_detalles === false) {
                                    die("Error al preparar la consulta de detalles: " . $conn->error);
                                }
                                $stmt_detalles->bind_param("i", $pedido['id']);
                                $stmt_detalles->execute();
                                $detalles_result = $stmt_detalles->get_result();
                                
                                if ($detalles_result->num_rows > 0):
                                    while ($detalle = $detalles_result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($detalle['nombre']) ?></td>
                                        <td><?= htmlspecialchars($detalle['cantidad']) ?></td>
                                    </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                    <tr>
                                        <td colspan="2" class="text-center">No hay productos para este pedido.</td>
                                    </tr>
                                <?php
                                endif;
                                $stmt_detalles->close();
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div id="map-container" style="display: none;" class="mt-5 p-4 bg-white rounded shadow-sm">
            <h3 class="text-center mb-3">Rastreo de Pedido (<span id="trackingOrderId"></span>)</h3>
            <p class="text-center text-muted">Repartidor asignado: <span id="trackingRepartidorName"></span></p>
            <div id="map"></div>
            <p class="text-center mt-3 mb-0">Última actualización: <span id="lastUpdate" class="font-weight-bold">N/A</span></p>
        </div>

    </div>

    <div class="whatsapp-float">
        <div class="mensaje">¿Tienes dudas? Comunícate por este medio</div>
        <a href="https://wa.me/51999999999" target="_blank" title="WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>

    <footer class="footer mt-5">
        <div class="container text-center">
            <p>© La Casa del Sazón. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="./js/mdb.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        let map;
        let repartidorMarker;
        let repartidorIcon;
        let currentRepartidorId = null;
        let currentOrderId = null;
        let trackingInterval; 

        function initMap() {
            if (map) return;

            const lima = [-12.046374, -77.042793];
            map = L.map('map', {
                scrollWheelZoom: true
            }).setView(lima, 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            repartidorIcon = L.divIcon({
                className: '',
                html: '<div class="delivery-marker"><i class="fas fa-truck"></i></div>',
                iconSize: [42, 42],
                iconAnchor: [21, 21]
            });
        }

        function showMap(orderId, repartidorId, repartidorNombre) {
            document.getElementById('map-container').style.display = 'block';
            document.getElementById('trackingOrderId').innerText = orderId;
            document.getElementById('trackingRepartidorName').innerText = repartidorNombre;

            currentRepartidorId = repartidorId;
            currentOrderId = orderId;
            initMap();
            setTimeout(() => map.invalidateSize(), 150);

            if (trackingInterval) {
                clearInterval(trackingInterval);
            }

            fetchRepartidorLocation(); 
            trackingInterval = setInterval(fetchRepartidorLocation, 5000);
        }

        function fetchRepartidorLocation() {
            if (!currentRepartidorId) {
                document.getElementById('lastUpdate').innerText = 'Repartidor no asignado.';
                if (repartidorMarker) repartidorMarker.remove();
                return;
            }

            fetch('obtener_ubicacion_repartidor.php?repartidor_id=' + currentRepartidorId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.location && data.location.latitud !== null && data.location.longitud !== null) {
                        const lat = parseFloat(data.location.latitud);
                        const lng = parseFloat(data.location.longitud); 

                        if (isNaN(lat) || isNaN(lng)) {
                            document.getElementById('lastUpdate').innerText = 'Ubicación inválida.';
                            if (repartidorMarker) repartidorMarker.remove();
                            return;
                        }

                        const position = [lat, lng];
                        if (!repartidorMarker) {
                            repartidorMarker = L.marker(position, {
                                icon: repartidorIcon,
                                title: 'Ubicación del Repartidor'
                            });
                        } else {
                            repartidorMarker.setLatLng(position);
                        }

                        if (!map.hasLayer(repartidorMarker)) {
                            repartidorMarker.addTo(map);
                        }
                        map.setView(position, 15);

                        const lastUpdateTimestamp = data.location.timestamp_ubicacion;
                        document.getElementById('lastUpdate').innerText = new Date(lastUpdateTimestamp).toLocaleString();

                    } else {
                        document.getElementById('lastUpdate').innerText = 'Repartidor sin ubicación o no disponible.';
                        if (repartidorMarker) repartidorMarker.remove(); 
                    }
                })
                .catch(error => {
                    console.error('Error al obtener la ubicación del repartidor:', error);
                    document.getElementById('lastUpdate').innerText = 'Error al cargar la ubicación.';
                    if (repartidorMarker) repartidorMarker.remove(); 
                });
        }
        
        // Función para mostrar/ocultar el popup de login (ya existente)
        function show_popup_login() {
            var popup = document.querySelector(".popup-login");
            if (popup.style.display === "block") {
                popup.style.display = "none";
            } else {
                popup.style.display = "block";
            }
        }

        // Código para cerrar el popup si se hace clic fuera de él
        window.onclick = function(event) {
            if (!event.target.matches('.btn-login-menu i') && !event.target.matches('.popup-login *')) {
                var popups = document.getElementsByClassName("popup-login");
                for (var i = 0; i < popups.length; i++) {
                    var openPopup = popups[i];
                    if (openPopup.style.display === "block") {
                        openPopup.style.display = "none";
                    }
                }
            }
        }
    </script>

</body>
</html>
