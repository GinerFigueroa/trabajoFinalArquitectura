<?php
session_start();

function validarEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) && strlen(trim($email)) > 0;
}

function validarBoton($boton)
{
    return isset($boton);    
}

if (isset($_POST['btnRecuperar']) && validarBoton($_POST['btnRecuperar'])) {
    if (validarEmail($_POST['txtEmail'])) {
        $email = strtolower(trim(htmlspecialchars($_POST['txtEmail'])));
        
        include_once('controlRecuperarPasword.php');
        $obcontrol = new controlRecuperarPasword();
        $obcontrol->procesarRecuperacion($email);
    } else {
        include_once('../../../shared/mensajeSistema.php'); 
        $objMensaje = new mensajeSistema();
        $objMensaje->mensajeSistemaShow(
            "El correo electrónico ingresado no es válido",
            "./indexRecuperarPasword.php",
            "error"
        ); 
    }
} else {
    include_once('../../../shared/mensajeSistema.php'); 
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow(
        "Acceso no permitido<br>Se ha detectado un intento de ingreso no autorizado",
        "./indexRecuperarPasword.php",
        "error"
    );
}
?>