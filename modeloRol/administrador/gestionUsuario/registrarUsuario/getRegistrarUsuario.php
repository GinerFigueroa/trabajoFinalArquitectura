<?php

session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controllResgistrar.php'); // Incluye el Controlador/Mediator

$objControl = new controlRegistroUsuario();
$objMensaje = new mensajeSistema();

/**
 * Patrón: BUILDER 🧱
 * Construye y formatea el array de datos del usuario a partir de $_POST.
 * Se asegura de que todos los campos esperados estén presentes, aunque vacíos.
 * @return array Datos del usuario.
 */
function buildUserDataFromPost() {
    // Uso de null-coalescing para seguridad y tipado básico
    return [
        'login' => trim($_POST['regUsuario'] ?? ''),
        'nombre' => trim($_POST['regNombre'] ?? ''),
        'apellidoPaterno' => trim($_POST['regApellidoPaterno'] ?? ''),
        'apellidoMaterno' => trim($_POST['regApellidoMaterno'] ?? ''),
        'email' => trim($_POST['regEmail'] ?? ''),
        'telefono' => trim($_POST['regTelefono'] ?? ''),
        'clave' => $_POST['regClave'] ?? '', // La clave no se trimea
        'idRol' => (int)($_POST['regRol'] ?? 0),
        'activo' => (int)($_POST['regActivo'] ?? 0)
    ];
}

/**
 * Patrón: CHAIN OF RESPONSIBILITY (Primer eslabón) 🔗
 * Valida la existencia de la acción y el método.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'registrar') {
    $objMensaje->mensajeSistemaShow("Acceso no autorizado o acción no válida. (Validación de POST)", '../indexGestionUsuario.php', 'error');
    exit();
}

// 1. Uso del BUILDER
$data = buildUserDataFromPost();

/**
 * CHAIN OF RESPONSIBILITY (Segundo eslabón) 🔗
 * Valida la completitud de los campos obligatorios.
 */
if (empty($data['login']) || empty($data['nombre']) || empty($data['apellidoPaterno']) || empty($data['email']) || empty($data['telefono']) || empty($data['clave']) || $data['idRol'] === 0) {
    $objMensaje->mensajeSistemaShow('Todos los campos obligatorios (usuario, nombre, apellidos, email, teléfono, clave y rol) deben ser completados.', './indexRegistroUsuario.php', 'error');
    exit();
}

// 2. Ejecución del COMMAND (Delegación al Controlador/Mediator)
// El controlador continuará con las validaciones de unicidad y complejidad.
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