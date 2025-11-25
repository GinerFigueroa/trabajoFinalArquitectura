<?php

session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEditarUsuario.php'); // Incluye el Controlador/Mediator

$objControl = new controlEditarUsuario();
$objMensaje = new mensajeSistema();

/**
 * Patrón: BUILDER 🧱
 * Construye y formatea el array de datos del usuario a partir de $_POST.
 * @return array Datos del usuario para edición.
 */
function buildUserDataFromPost(): array {
    // Recolectar datos asegurando valores por defecto o tipado correcto
    return [
        'idUsuario' => (int)($_POST['idUsuario'] ?? 0),
        'login' => trim($_POST['editUsuario'] ?? ''),
        'nombre' => trim($_POST['editNombre'] ?? ''),
        'apellidoPaterno' => trim($_POST['editApellidoPaterno'] ?? ''),
        'apellidoMaterno' => trim($_POST['editApellidoMaterno'] ?? ''),
        'email' => trim($_POST['editEmail'] ?? ''),
        'telefono' => trim($_POST['editTelefono'] ?? ''),
        'clave' => $_POST['editClave'] ?? '', // Clave puede estar vacía si no se cambia
        'idRol' => (int)($_POST['editRol'] ?? 0),
        'activo' => (int)($_POST['editActivo'] ?? 0)
    ];
}

/**
 * Patrón: CHAIN OF RESPONSIBILITY (Primer eslabón) 🔗
 * Valida el método de acceso y la acción enviada.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'editar') {
    $objMensaje->mensajeSistemaShow("Acceso no autorizado o acción no válida. (Validación de POST)", '../indexGestionUsuario.php', 'error');
    exit();
}

// 1. Uso del BUILDER
$data = buildUserDataFromPost();

/**
 * CHAIN OF RESPONSIBILITY (Segundo eslabón - Validación mínima) 🔗
 * Verifica que el ID de usuario y el rol sean válidos antes de pasar al controlador.
 */
if ($data['idUsuario'] <= 0 || empty($data['login']) || $data['idRol'] === 0) {
    // Si el ID falla, volvemos a la lista, no podemos volver al formulario de edición sin ID
    $objMensaje->mensajeSistemaShow('Faltan datos obligatorios (ID, Usuario o Rol) para la edición.', '../indexGestionUsuario.php', 'error');
    exit();
}

// 2. Ejecución del COMMAND (Delegación al Controlador/Mediator)
$objControl->editarUsuario(
    $data['idUsuario'],
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