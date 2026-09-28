<?php
session_start();
include __DIR__ . "/conexion.php"; 

// Mostrar errores de PHP (solo en desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json'); 

$response = ['success' => false, 'message' => ''];

// Verifica si la conexión a la base de datos es exitosa
if (!db_disponible()) {
    $response['message'] = 'Error de conexión a la base de datos.';
    echo json_encode($response);
    exit();
}

// Verifica si los datos necesarios se han enviado por POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $repartidor_id = isset($_POST['repartidor_id']) ? intval($_POST['repartidor_id']) : 0;
    $latitud = isset($_POST['latitud']) ? floatval($_POST['latitud']) : 0.0;
    $longitud = isset($_POST['longitud']) ? floatval($_POST['longitud']) : 0.0;

    // Validaciones básicas de los datos
    if ($repartidor_id <= 0 || $latitud == 0.0 || $longitud == 0.0) {
        $response['message'] = 'Datos de ubicación incompletos o inválidos. Asegúrate de enviar repartidor_id, latitud y longitud válidos.';
        echo json_encode($response);
        exit();
    }

    $check = $conn->prepare("SELECT id FROM repartidores WHERE id = ? LIMIT 1");
    $check->bind_param("i", $repartidor_id);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows === 0) {
        $response['message'] = 'El repartidor indicado no existe.';
        echo json_encode($response);
        $check->close();
        exit();
    }
    $check->close();

    $stmt = $conn->prepare("INSERT INTO ubicaciones_repartidores (repartidor_id, latitud, longitud) VALUES (?, ?, ?)");
    
    $stmt->bind_param("idd", $repartidor_id, $latitud, $longitud);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Ubicación del repartidor actualizada correctamente.';
    } else {
        $response['message'] = 'Error al actualizar la ubicación: ' . $stmt->error;
    }
    $stmt->close();

} else {
    $response['message'] = 'Método de solicitud no permitido. Usa POST para enviar la ubicación.';
}

echo json_encode($response);
exit();
?>
