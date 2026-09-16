<?php
include __DIR__ . "/conexion.php";

header("Content-Type: application/json");

$response = [
    "success" => false,
    "repartidores" => [],
    "message" => ""
];

if ($conn->connect_error) {
    $response["message"] = "Error de conexion a la base de datos.";
    echo json_encode($response);
    exit();
}

$query = "SELECT id, nombre FROM repartidores WHERE estado_disponibilidad IN ('disponible', 'ocupado') ORDER BY nombre ASC";
$result = $conn->query($query);

if (!$result) {
    $response["message"] = "No se pudieron cargar los repartidores.";
    echo json_encode($response);
    exit();
}

while ($row = $result->fetch_assoc()) {
    $response["repartidores"][] = [
        "id" => (int) $row["id"],
        "nombre" => $row["nombre"]
    ];
}

$response["success"] = true;
$response["message"] = "Repartidores cargados correctamente.";

echo json_encode($response);
?>
