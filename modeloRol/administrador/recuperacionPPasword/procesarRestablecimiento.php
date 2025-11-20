<?php
session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('./controlRecuperarPasword.php');
include_once('../../../modelo/securitUsuario.php');

$objMensaje = new mensajeSistema();

if (isset($_POST['btnRestablecer'])) {
    $idUsuario = $_POST['idUsuario'] ?? null;
    $nuevaPassword = $_POST['nuevaPassword'] ?? '';
    $confirmarPassword = $_POST['confirmarPassword'] ?? '';
    $parametrosCodificados = $_POST['data'] ?? '';
    
    // Validar que el enlace sigue siendo válido
    if (!controlRecuperarPasword::validarEnlaceRecuperacion($parametrosCodificados)) {
        $objMensaje->mensajeSistemaShow("El enlace de recuperación ha expirado", "../index.php", "error");
        exit();
    }
    
    // Validar contraseñas
    if (empty($nuevaPassword) || strlen($nuevaPassword) < 6) {
        $objMensaje->mensajeSistemaShow("La contraseña debe tener al menos 6 caracteres", "restablecerPassword.php?data=" . urlencode($parametrosCodificados), "error");
        exit();
    }
    
    if ($nuevaPassword !== $confirmarPassword) {
        $objMensaje->mensajeSistemaShow("Las contraseñas no coinciden", "restablecerPassword.php?data=" . urlencode($parametrosCodificados), "error");
        exit();
    }
    
    // Actualizar contraseña
    $objUsuario = new UsuarioDAO();
    $passwordHash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
    $resultado = $objUsuario->actualizarPassword($idUsuario, $passwordHash);
    
    if ($resultado) {
        $objMensaje->mensajeSistemaShow("Contraseña restablecida exitosamente. Ahora puede iniciar sesión con su nueva contraseña.", "../index.php", "success");
    } else {
        $objMensaje->mensajeSistemaShow("Error al restablecer la contraseña", "restablecerPassword.php?data=" . urlencode($parametrosCodificados), "error");
    }
} else {
    $objMensaje->mensajeSistemaShow("Acceso no permitido", "../index.php", "error");
}
?>