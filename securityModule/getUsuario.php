<?php

session_start();

/**
 * Patrón: Helper/Utilidad (Funciones de Validación)
 */
function validarBoton($boton)
{
    return isset($boton);    
}

function validarTexto($txtLogin, $txtPassword)
{
    $loginLength = strlen(trim($txtLogin));
    $passwordLength = strlen(trim($txtPassword));
    return ($loginLength > 3 && $passwordLength > 3);
}

// 1. Lógica del Front Controller
if (isset($_POST['btnAceptar']) && validarBoton($_POST['btnAceptar'])) {
    // 2. Validación de la Entrada (Delegación a funciones Helper)
    if (validarTexto($_POST['txtLogin'], $_POST['txtPassword'])) {
        $login = strtolower(trim(htmlspecialchars($_POST['txtLogin'])));
        $password = trim(htmlspecialchars($_POST['txtPassword']));

        // 3. Delegación al Controlador
        include_once('controlAutenticarUsuario.php');
        $obcontrol = new controlAutenticarUsuario();
        $obcontrol->verificarUsuario($login, $password); // Ejecución del Command
    } else {
        // Delegación a la utilidad de Mensajes
        include_once('../shared/mensajeSistema.php'); 
        $objMensaje = new mensajeSistema();
        $objMensaje->mensajeSistemaShow(
            "Los datos ingresados no son válidos<br>El login y password deben tener más de 3 caracteres",
            "../index.php",
            "error"
        ); 
    }
} else {
    // Delegación a la utilidad de Mensajes
    include_once('../shared/mensajeSistema.php'); 
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow(
        "Acceso no permitido<br>Se ha detectado un intento de ingreso no autorizado",
        "../index.php",
        "error"
    );
}
?>