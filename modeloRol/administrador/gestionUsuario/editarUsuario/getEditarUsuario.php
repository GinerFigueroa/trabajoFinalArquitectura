<?php
// C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\administrador\gestionUsuario\editarUsuario\getEditarUsuario.php
session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEditarUsuario.php'); 

$objControl = new controlEditarUsuario();
$objMensaje = new mensajeSistema();

// Emulación del patrón BUILDER
function buildUserDataFromPost() {
    return [
        'idUsuario' => (int)($_POST['idUsuario'] ?? 0),
        'login' => trim($_POST['editUsuario'] ?? ''),
        'nombre' => trim($_POST['editNombre'] ?? ''),
        'apellidoPaterno' => trim($_POST['editApellidoPaterno'] ?? ''),
        'apellidoMaterno' => trim($_POST['editApellidoMaterno'] ?? ''),
        'email' => trim($_POST['editEmail'] ?? ''),
        'telefono' => trim($_POST['editTelefono'] ?? ''),
        'clave' => $_POST['editClave'] ?? '',
        'idRol' => (int)($_POST['editRol'] ?? 0),
        'activo' => (int)($_POST['editActivo'] ?? 0)
    ];
}

// Emulación del patrón CHAIN OF RESPONSIBILITY
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'editar') {
    $objMensaje->mensajeSistemaShow("Acceso no autorizado o acción no válida. (CHAIN/POST)", '../indexGestionUsuario.php', 'error');
    exit();
}

// 1. Uso del BUILDER
$data = buildUserDataFromPost();

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