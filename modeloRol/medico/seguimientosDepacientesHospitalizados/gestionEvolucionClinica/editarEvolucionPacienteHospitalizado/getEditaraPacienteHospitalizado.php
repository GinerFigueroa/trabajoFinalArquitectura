<?php
session_start();
include_once('./controlEditarPacienteHospitalizado.php');
include_once('../../../../../shared/mensajeSistema.php');

$objControl = new controlEditarPacienteHospitalizado(); // Mediator
$objMensaje = new mensajeSistema(); // Dependency (Mensajería)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Atributo: `$idSeguimiento` (PK)
    $idSeguimiento = isset($_POST['idSeguimiento']) ? (int)$_POST['idSeguimiento'] : null;

    if (empty($idSeguimiento)) {
        $objMensaje->mensajeSistemaShow("ID de registro de seguimiento faltante o no válido.", "../indexEvolucionClinicaPacienteHospitalizado.php", "error");
        exit();
    }
    
    // Recoger y limpiar datos del formulario
    $data = [
        'idSeguimiento' => $idSeguimiento,
        // Atributo: `$idInternado`
        'idInternado' => isset($_POST['idInternado']) ? (int)$_POST['idInternado'] : null,
        // Atributo: `$idMedico` (ID de usuario)
        'idMedico' => isset($_POST['idMedico']) ? (int)$_POST['idMedico'] : null,
        // Atributo: `$idEnfermera` (ID de usuario, opcional)
        'idEnfermera' => isset($_POST['idEnfermera']) && !empty($_POST['idEnfermera']) ? (int)$_POST['idEnfermera'] : null,
        // Atributo: `$evolucion`
        'evolucion' => isset($_POST['evolucion']) ? trim($_POST['evolucion']) : '',
        // Atributo: `$tratamiento`
        'tratamiento' => isset($_POST['tratamiento']) ? trim($_POST['tratamiento']) : '',
    ];

    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    // Método: `ejecutarComando`
    $objControl->ejecutarComando('editar', $data);
    
} else {
    // Si no es POST, redirigir al formulario principal de gestión
    header("Location: ../indexEvolucionClinicaPacienteHospitalizado.php");
    exit();
}
?>