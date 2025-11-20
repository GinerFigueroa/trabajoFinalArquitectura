<?php

include_once('../../../../../modelo/InternadoSeguimientoDAO.php');

include_once('../../../../../shared/mensajeSistema.php');

class controlEditarPacienteHospitalizado
{
    private $objSeguimientoDAO;
    private $objAuxiliarDAO; // Nuevo objeto auxiliar
    private $objMensaje;

    public function __construct()
    {
        $this->objSeguimientoDAO = new InternadoSeguimientoDAO(); 
        $this->objAuxiliarDAO = new EntidadAuxiliarDAO(); // Instanciamos el auxiliar
        $this->objMensaje = new mensajeSistema();
    }

public function editarEvolucion($idSeguimiento, $idInternado, $idUsuarioMedico, $idUsuarioEnfermera, $evolucion, $tratamiento)
{
    $rutaRetorno = "./indexEditarPacienteHospitazado.php?id={$idSeguimiento}";
    
    // 1. Validaciones básicas
    if (empty($idSeguimiento) || empty($idInternado) || empty($idUsuarioMedico) || empty($evolucion)) {
        $this->objMensaje->mensajeSistemaShow("Los campos obligatorios están incompletos.", $rutaRetorno, 'systemOut', false);
        return;
    }

    // 2. Validar que el seguimiento existe
    if (!$this->objSeguimientoDAO->obtenerSeguimientoPorId($idSeguimiento)) {
        $this->objMensaje->mensajeSistemaShow("El registro de evolución no existe.", "../indexEvolucionClinicaPacienteHospitalizado.php", "error");
        return;
    }

    // 3. Validar que el usuario médico existe y es médico
    if (!$this->objAuxiliarDAO->validarUsuarioEsMedico($idUsuarioMedico)) {
        $this->objMensaje->mensajeSistemaShow("El usuario seleccionado como médico no es un médico válido.", $rutaRetorno, 'systemOut', false);
        return;
    }

    // 4. Convertir id_usuario_medico a id_medico
    $idMedico = $this->objAuxiliarDAO->obtenerIdMedicoPorIdUsuario($idUsuarioMedico);
    if (!$idMedico) {
        $this->objMensaje->mensajeSistemaShow("Error al obtener el ID médico del usuario seleccionado.", $rutaRetorno, 'systemOut', false);
        return;
    }

    // 5. Validar enfermera (si se seleccionó)
    $idEnfermera = null;
    if (!empty($idUsuarioEnfermera)) {
        if (!$this->objAuxiliarDAO->validarUsuarioEsEnfermera($idUsuarioEnfermera)) {
            $this->objMensaje->mensajeSistemaShow("El usuario seleccionado como enfermera no es una enfermera válida.", $rutaRetorno, 'systemOut', false);
            return;
        }
        $idEnfermera = $idUsuarioEnfermera;
    }

    // 6. Validar internado
    if (!$this->objAuxiliarDAO->obtenerNombrePacientePorInternado($idInternado)) {
        $this->objMensaje->mensajeSistemaShow("El ID de Internado no es válido.", $rutaRetorno, 'systemOut', false);
        return;
    }

    // 7. Actualizar seguimiento
    $resultado = $this->objSeguimientoDAO->editarSeguimiento(
        $idSeguimiento,
        $idInternado, 
        $idMedico,        // id_medico (de la tabla medicos)
        $idEnfermera,     // id_usuario (directo de la tabla usuarios)
        $evolucion, 
        $tratamiento
    );

    if ($resultado) {
        $this->objMensaje->mensajeSistemaShow('Evolución clínica actualizada correctamente.', '../indexEvolucionClinicaPacienteHospitalizado.php', 'success');
    } else {
        $this->objMensaje->mensajeSistemaShow('Error al actualizar la evolución.', $rutaRetorno, 'error');
    }
}
}
?>