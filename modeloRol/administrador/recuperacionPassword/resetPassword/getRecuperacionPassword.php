<?php
// recuperacionPassword/resetPassword/getResetPassword.php
// 🛡️ CHAIN OF RESPONSIBILITY + 🧪 COMMAND

session_start();

include_once('../../../../config/database.php');
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlResetPassword.php');

$objControl = new controlResetPassword();
$objMensaje = new mensajeSistema();

try {
    // 🛡️ Validaciones básicas
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("❌ Método no permitido");
    }

    if (!isset($_POST['btnResetPassword']) || !isset($_POST['token'])) {
        throw new Exception("❌ Datos incompletos");
    }

    $token = $_POST['token'];
    $nuevaClave = $_POST['nueva_clave'];
    $confirmarClave = $_POST['confirmar_clave'];

    // Validar que las contraseñas coincidan
    if ($nuevaClave !== $confirmarClave) {
        throw new Exception("❌ Las contraseñas no coinciden");
    }

    // Validar fortaleza de contraseña
    if (strlen($nuevaClave) < 8) {
        throw new Exception("❌ La contraseña debe tener al menos 8 caracteres");
    }

    // 🧪 COMMAND PATTERN - Procesar reset
    $ip = $_SERVER['REMOTE_ADDR'];
    $resultado = $objControl->resetearPassword($token, $nuevaClave, $ip);
    
    if ($resultado) {
        $objMensaje->mensajeSistemaShow(
            "✅ Contraseña actualizada exitosamente. Ahora puedes iniciar sesión con tu nueva contraseña.", 
            "../../index.php", 
            "success"
        );
    } else {
        throw new Exception("❌ Error al actualizar la contraseña");
    }
    
} catch (Exception $e) {
    $token = $_POST['token'] ?? '';
    $redirectUrl = !empty($token) ? 
        "./indexResetPassword.php?token=" . urlencode($token) : 
        "../indexRecuperacionPassword.php";
        
    $objMensaje->mensajeSistemaShow(
        $e->getMessage(), 
        $redirectUrl, 
        "error"
    );
}
?>