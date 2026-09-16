<?php
session_start();
include __DIR__ . "/conexion.php"; 

// Mostrar errores de PHP (solo en desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json'); 

$response = ['success' => false, 'message' => ''];

// Verifica si la conexión a la base de datos es exitosa
if ($conn->connect_error) { 
    $response['message'] = 'Error de conexión a la base de datos: ' . $conn->connect_error;
    echo json_encode($response);
    exit();
}

if (isset($_GET['repartidor_id'])) {
    $repartidor_id = intval($_GET['repartidor_id']);

    if ($repartidor_id <= 0) {
        $response['message'] = 'ID de repartidor inválido.';
        echo json_encode($response);
        exit();
    }

    // Seleccionar la última ubicación basándose en el timestamp
    $stmt = $conn->prepare("SELECT latitud, longitud, timestamp_ubicacion FROM ubicaciones_repartidores WHERE repartidor_id = ? ORDER BY timestamp_ubicacion DESC LIMIT 1");
    if ($stmt === false) {
        $response['message'] = 'Error al preparar la consulta: ' . $conn->error;
        echo json_encode($response);
        exit();
    }
    $stmt->bind_param("i", $repartidor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $location = $result->fetch_assoc();
        $response['success'] = true;
        $response['location'] = $location;
        $response['message'] = 'Ubicación encontrada.';
    } else {
        $response['message'] = 'Ubicación del repartidor no encontrada.';
    }
    $stmt->close();

} else {
    $response['message'] = 'ID del repartidor no proporcionado.';
}

// Cierra la conexión a la base de datos
$conn->close();

echo json_encode($response);
exit();
?>