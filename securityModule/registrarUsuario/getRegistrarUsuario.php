<?php

session_start();

include_once('../../shared/mensajeSistema.php');
include_once('./controllResgistrar.php'); // Se incluye el controlador de negocio

$objControl = new controlRegistroUsuario();
$objMensaje = new mensajeSistema();

// Emulación del patrón BUILDER: Construye el array de datos
function buildUserDataFromPost() {
    // Se usa el operador null-coalescing para asignar valor por defecto
    return [
        'login' => trim($_POST['regUsuario'] ?? ''),
        'nombre' => trim($_POST['regNombre'] ?? ''),
        'apellidoPaterno' => trim($_POST['regApellidoPaterno'] ?? ''),
        'apellidoMaterno' => trim($_POST['regApellidoMaterno'] ?? ''),
        'email' => trim($_POST['regEmail'] ?? ''),
        'telefono' => trim($_POST['regTelefono'] ?? ''),
        'clave' => $_POST['regClave'] ?? '',
        'idRol' => (int)($_POST['regRol'] ?? 0),
        'activo' => (int)($_POST['regActivo'] ?? 0)
    ];
}

// Emulación del patrón CHAIN OF RESPONSIBILITY: Valida la existencia de datos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'registrar') {
    // 🚨 ESTO SE EJECUTA SI FALLA CUALQUIERA DE LAS CONDICIONES
    $objMensaje->mensajeSistemaShow("Acceso no autorizado o acción no válida. (CHAIN/POST)", '
    ../indexLoginSegurity.php', 'error');
    exit();

}

// 1. Uso del BUILDER
$data = buildUserDataFromPost();

// 2. Validación de campos obligatorios (CHAIN simplificado)
if (empty($data['login']) || empty($data['nombre']) || empty($data['apellidoPaterno']) || empty($data['email']) || empty($data['telefono']) || empty($data['clave']) || empty($data['idRol'])) {
    $objMensaje->mensajeSistemaShow('Todos los campos obligatorios deben ser completados. (CHAIN/DATA)', '
    ../indexLoginSegurity.php', 'systemOut', false);
    exit();
}

// 3. Ejecución del COMMAND (Delegación al Controlador/Mediator)
$objControl->registrarUsuario(
    $data['login'],
    $data['nombre'],
    $data['apellidoPaterno'],
    $data['apellidoMaterno'],
    $data['email'],
    $data['telefono'],
    $data['clave'],
    $data['idRol'],
    $data['activo']
);
?>