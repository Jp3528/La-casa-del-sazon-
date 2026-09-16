<?php
session_start();
include("conexion.php");

$redirigir = $_POST["redirigir"] ?? "index.php";

// Cerrar sesión
if (isset($_POST["cerrar_sesion"])) {
    session_unset();
    session_destroy();
    header("Location: $redirigir");
    exit;
}

// Iniciar sesión
if (isset($_POST["login_email_cliente"], $_POST["login_clave_cliente"])) {
    $correo = $_POST["login_email_cliente"];
    $clave = $_POST["login_clave_cliente"];

    if (!empty($correo) && !empty($clave)) {
        $stmt = $conn->prepare("SELECT id, nombre, password FROM clientes WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            if (password_verify($clave, $usuario["password"])) {
                $_SESSION["cliente_id"] = $usuario["id"];
                $_SESSION["cliente_nombre"] = $usuario["nombre"];
                header("Location: $redirigir");
                exit;
            }
        }
    }
}

// Error de login
header("Location: $redirigir?login_error=1");
exit;
?>