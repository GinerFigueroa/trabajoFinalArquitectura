<?php
session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('./controlRecuperarPasword.php');

$objMensaje = new mensajeSistema();

// Verificar que el usuario viene del proceso de recuperación
if (!isset($_SESSION['recuperacion_usuario_id'])) {
    $objMensaje->mensajeSistemaShow(
        "Acceso no permitido", 
        "./indexRecuperarPasword.php", 
        "error"
    );
    exit();
}

if (isset($_POST['btnRestablecer'])) {
    $codigo = $_POST['codigo'] ?? '';
    $nuevaPassword = $_POST['nueva_password'] ?? '';
    $confirmarPassword = $_POST['confirmar_password'] ?? '';
    
    // Validaciones básicas
    if (empty($codigo) || empty($nuevaPassword) || empty($confirmarPassword)) {
        $objMensaje->mensajeSistemaShow(
            "Todos los campos son obligatorios", 
            "./restablecerPassword.php", 
            "error"
        );
        return;
    }
    
    if ($nuevaPassword !== $confirmarPassword) {
        $objMensaje->mensajeSistemaShow(
            "Las contraseñas no coinciden", 
            "./restablecerPassword.php", 
            "error"
        );
        return;
    }
    
    $idUsuario = $_SESSION['recuperacion_usuario_id'];
    
    $objControl = new controlRecuperarPasword();
    $resultado = $objControl->verificarCodigoYRestablecer($idUsuario, $codigo, $nuevaPassword);
    
    if ($resultado['success']) {
        $objMensaje->mensajeSistemaShow(
            $resultado['message'], 
            "../../../index.php", 
            "success"
        );
    } else {
        $objMensaje->mensajeSistemaShow(
            $resultado['message'], 
            "./restablecerPassword.php", 
            "error"
        );
    }
} else {
    $objMensaje->mensajeSistemaShow(
        "Acceso no permitido", 
        "./indexRecuperarPasword.php", 
        "error"
    );
}
?>