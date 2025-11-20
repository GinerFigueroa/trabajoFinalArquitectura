<?php
// recuperacionPassword/getRecuperacionPassword.php
// 🛡️ CHAIN OF RESPONSIBILITY + 🧪 COMMAND

session_start();

// Incluir dependencias
include_once('../../../modelo/securitUsuario.php');
include_once('../../../shared/mensajeSistema.php');
include_once('./controlRecuperacionPassword.php');

$objControl = new controlRecuperacionPassword();
$objMensaje = new mensajeSistema();

try {
    // 🛡️ Validaciones básicas
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("❌ Método no permitido");
    }

    if (!isset($_POST['btnSolicitarRecuperacion']) || !isset($_POST['email'])) {
        throw new Exception("❌ Datos incompletos");
    }

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("❌ Email inválido");
    }

    // 🧪 COMMAND PATTERN - Procesar solicitud
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $resultado = $objControl->solicitarRecuperacion($email, $ip, $userAgent);
    
    if ($resultado) {
        // Por seguridad, siempre mostramos el mismo mensaje
        $objMensaje->mensajeSistemaShow(
            "📧 Si el email existe en nuestro sistema, recibirás un enlace de recuperación en los próximos minutos. " .
            "Revisa tu bandeja de entrada y carpeta de spam.", 
            "../index.php", 
            "success"
        );
    } else {
        throw new Exception("❌ Error al procesar la solicitud");
    }
    
} catch (Exception $e) {
    $objMensaje->mensajeSistemaShow(
        $e->getMessage(), 
        "./indexRecuperacionPassword.php", 
        "error"
    );
}
?>