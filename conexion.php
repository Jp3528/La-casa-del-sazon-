<?php
$host = getenv("DB_HOST") ?: "127.0.0.1";
$user = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASSWORD") ?: "";
$db = getenv("DB_NAME") ?: "restaurante";

$conn = null;

if (class_exists("mysqli") && function_exists("mysqli_report")) {
    mysqli_report(MYSQLI_REPORT_OFF);

    try {
        $conn = @new mysqli($host, $user, $password, $db);
        if ($conn && $conn->connect_errno) {
            $conn = null;
        } else if ($conn) {
            $conn->set_charset("utf8");
        }
    } catch (Throwable $e) {
        $conn = null;
    }
}

function db_disponible(): bool {
    global $conn;
    return class_exists("mysqli") && $conn instanceof mysqli;
}

function productos_demo(): array {
    return [
        [
            "id" => 1,
            "nombre" => "Arroz con pato",
            "descripcion" => "Arroz cocido en fondo de cilantro con tierno pato.",
            "precio" => 24.00,
            "stock" => 10,
            "imagen" => "arrozconpato.webp",
        ],
        [
            "id" => 2,
            "nombre" => "Papa rellena",
            "descripcion" => "Tradicional platillo peruano elaborado con pure de papa y relleno sazonado.",
            "precio" => 10.00,
            "stock" => 8,
            "imagen" => "platillo2.jpg",
        ],
        [
            "id" => 3,
            "nombre" => "Alfajores iquenos",
            "descripcion" => "Dulces tradicionales rellenos de manjar blanco y espolvoreados con azucar.",
            "precio" => 10.00,
            "stock" => 5,
            "imagen" => "platillo3.jpg",
        ],
        [
            "id" => 4,
            "nombre" => "Hamburguesa Halloween Whopper",
            "descripcion" => "Hamburguesa especial con carne, queso y salsa de la casa.",
            "precio" => 17.00,
            "stock" => 6,
            "imagen" => "platillo4.jpg",
        ],
        [
            "id" => 5,
            "nombre" => "Ceviche Mixto",
            "descripcion" => "Pescado y mariscos marinados con limon, cebolla y aji.",
            "precio" => 30.00,
            "stock" => 12,
            "imagen" => "platillo5.jpg",
        ],
        [
            "id" => 6,
            "nombre" => "Encebollado de pollo",
            "descripcion" => "Pollo jugoso en salsa de cebolla con guarnicion.",
            "precio" => 28.50,
            "stock" => 9,
            "imagen" => "platillo6.jpg",
        ],
    ];
}

function obtener_productos(?int $limite = null): array {
    global $conn;

    if (db_disponible()) {
        $sql = "SELECT * FROM productos WHERE stock > 0";
        if ($limite !== null) {
            $sql .= " LIMIT " . intval($limite);
        }

        $resultado = $conn->query($sql);
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        }
    }

    $productos = productos_demo();
    return $limite === null ? $productos : array_slice($productos, 0, $limite);
}

function mensaje_db_no_disponible(): string {
    return "La base de datos no esta conectada. La vista publica se muestra con datos demo.";
}
?>
