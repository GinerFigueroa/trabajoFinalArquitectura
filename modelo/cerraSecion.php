<?php
/**
 * Módulo de Seguridad: Maneja acciones como el cierre de sesión (logout).
 */

// 1. INICIAR LA SESIÓN
// Es esencial llamar a session_start() para poder manipular las variables de sesión ($_SESSION)
// y acceder a las funciones de destrucción de sesión.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. VERIFICAR LA ACCIÓN SOLICITADA
// Comprueba si el parámetro 'action' está presente en la URL (e.g., ?action=logout)
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // 3. LÓGICA PARA CERRAR SESIÓN (Logout)
    if ($action === 'logout') {
        
        // A. Limpiar el array de sesión
        // Borra todas las variables de sesión registradas.
        $_SESSION = array();

        // B. Destruir la cookie de sesión
        // Si se están usando cookies de sesión, se borra el identificador de la sesión.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // C. Destruir la sesión en el servidor
        // Esto elimina el archivo de sesión del sistema.
        session_destroy();

        // D. Redirigir al usuario
        // Después de destruir la sesión, el usuario es enviado a la página pública.
        // La ruta '.. /securityModule/indexLoginSegurity.php' es correcta si 'securityModule'
        // está al mismo nivel que 'modelo' (es decir, en el directorio 'TRABAJOFINALARQUITECTURA').
        header("Location: ../securityModule/indexLoginSegurity.php"); 
        exit; // Termina el script para asegurar que la redirección se ejecute inmediatamente.
    }
    
    // Aquí puedes añadir más acciones de seguridad si las necesitas...
    /*
    elseif ($action === 'check_status') {
        // Lógica para verificar el estado de la sesión
    }
    */
} else {
    // Si se accede al archivo sin una acción, se puede redirigir o mostrar un error.
    // header("Location: /dashboard.php"); 
    // exit;
}

?>