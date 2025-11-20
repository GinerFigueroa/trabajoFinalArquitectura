<?php
// Archivo: getEmisionBoleta.php (Gateway para acciones de la lista principal)

session_start();

include_once('../../../shared/mensajeSistema.php');
include_once('./controlEmisionBoleta.php'); // Usamos el control principal

$objControl = new controlEmisionBoleta();
$objMensaje = new mensajeSistema();

$action = $_GET['action'] ?? null;
$idBoleta = isset($_GET['id']) ? (int)$_GET['id'] : null;

$urlRedireccion = "./indexEmisionBoletaFinal.php";

if ($action == 'eliminar' && $idBoleta) {
    if (!is_numeric($idBoleta)) {
        $objMensaje->mensajeSistemaShow("ID de boleta no válido.", $urlRedireccion, "error");
    } else {
        $objControl->eliminarBoleta($idBoleta);
    }
} else {
    // Si no es una acción de eliminación, redirigir al listado
    header("Location: {$urlRedireccion}");
    exit();
}
?>