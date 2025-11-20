<?php
include_once("../../../../../modelo/InternadoSeguimientoDAO.php"); 
include_once('../../../../../shared/mensajeSistema.php');

class controlAgregarEvolucionPaciente
{
    private $objSeguimientoDAO;
    private $objAuxiliarDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objSeguimientoDAO = new InternadoSeguimientoDAO(); 
        $this->objAuxiliarDAO = new EntidadAuxiliarDAO(); // Asegurar que se instancia correctamente
        $this->objMensaje = new mensajeSistema();
    }

  public function registrarEvolucion($idInternado, $idUsuarioMedico, $idUsuarioEnfermera, $evolucion, $tratamiento)
{
    $rutaRetorno = './indexaAgregarEvolucionPaciente.php';
    $evolucion = trim($evolucion);
    $tratamiento = trim($tratamiento);
    
    // 1. Validaciones básicas
    if (empty($idInternado) || empty($idUsuarioMedico) || empty($evolucion)) {
        $this->objMensaje->mensajeSistemaShow("Los campos Paciente Hospitalizado, Médico Tratante y Evolución Clínica son obligatorios.", $rutaRetorno, 'systemOut', false);
        return;
    }

    // 2. Validar que el usuario médico existe y es médico
    if (!$this->objAuxiliarDAO->validarUsuarioEsMedico($idUsuarioMedico)) {
        $this->objMensaje->mensajeSistemaShow("El usuario seleccionado como médico no es un médico válido.", $rutaRetorno, 'systemOut', false);
        return;
    }

    // 3. Convertir id_usuario_medico a id_medico
    $idMedico = $this->objAuxiliarDAO->obtenerIdMedicoPorIdUsuario($idUsuarioMedico);
    if (!$idMedico) {
        $this->objMensaje->mensajeSistemaShow("Error al obtener el ID médico del usuario seleccionado.", $rutaRetorno, 'systemOut', false);
        return;
    }

    // 4. Validar enfermera (si se seleccionó)
    $idEnfermera = null;
    if (!empty($idUsuarioEnfermera)) {
        if (!$this->objAuxiliarDAO->validarUsuarioEsEnfermera($idUsuarioEnfermera)) {
            $this->objMensaje->mensajeSistemaShow("El usuario seleccionado como enfermera no es una enfermera válida.", $rutaRetorno, 'systemOut', false);
            return;
        }
        $idEnfermera = $idUsuarioEnfermera; // Se usa id_usuario directamente
    }

    // 5. Validar internado
    if (!$this->objAuxiliarDAO->obtenerNombrePacientePorInternado($idInternado)) {
        $this->objMensaje->mensajeSistemaShow("El ID de Internado seleccionado no es válido o no existe.", $rutaRetorno, 'systemOut', false);
        return;
    }

    // 6. Registrar seguimiento
    $resultado = $this->objSeguimientoDAO->registrarSeguimiento(
        $idInternado, 
        $idMedico,        // id_medico (de la tabla medicos)
        $idEnfermera,     // id_usuario (directo de la tabla usuarios)
        $evolucion, 
        $tratamiento
    );

    if ($resultado) {
        $this->objMensaje->mensajeSistemaShow('Evolución clínica registrada correctamente.', '../indexEvolucionClinicaPacienteHospitalizado.php', 'success');
    } else {
        $this->objMensaje->mensajeSistemaShow('Error al registrar la evolución. Fallo en la inserción en la base de datos.', $rutaRetorno, 'error');
    }
}

}
?>