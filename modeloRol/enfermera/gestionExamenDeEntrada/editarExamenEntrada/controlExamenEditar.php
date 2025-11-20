<?php
include_once("../../../modelo/ExamenClinicoDAO.php");
include_once('../../../../shared/mensajeSistema.php');

class controlEditarPacienteHospitalizado
{
    private $objSeguimientoDAO;
    private $objAuxiliarDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objSeguimientoDAO = new InternadoSeguimientoDAO(); 
        $this->objAuxiliarDAO = new EntidadAuxiliarDAO(); 
        $this->objMensaje = new mensajeSistema();
    }

    public function editarEvolucion($idSeguimiento, $idInternado, $idMedico, $idEnfermera, $evolucion, $tratamiento)
    {
        $rutaRetorno = "./indexEditarPacienteHospitazado.php?id={$idSeguimiento}";
        $evolucion = trim($evolucion);
        $tratamiento = trim($tratamiento);
        
        // --- 1. Validaciones de Campo Vacío (Obligatorias) ---
        if (empty($idSeguimiento)) {
             $this->objMensaje->mensajeSistemaShow("Error: ID de registro de seguimiento faltante o no válido.", "../indexEvolucionClinicaPacienteHospitalizado.php", "error");
            return;
        }
        if (empty($idInternado) || empty($idMedico) || empty($evolucion)) {
            $this->objMensaje->mensajeSistemaShow("Los campos **Paciente Hospitalizado**, **Médico Tratante** y **Evolución Clínica** son obligatorios.", $rutaRetorno, 'systemOut', false);
            return;
        }

        // --- 2. Validaciones de Existencia de Entidades (Integridad de Datos) ---

        // a) Validar que el ID de Seguimiento a editar exista
        if (!$this->objSeguimientoDAO->obtenerSeguimientoPorId($idSeguimiento)) {
             $this->objMensaje->mensajeSistemaShow("El registro de evolución con ID **{$idSeguimiento}** no existe en la base de datos.", "../indexEvolucionClinicaPacienteHospitalizado.php", "error");
            return;
        }

        // b) Validar que el ID de Internado sea válido
        if (!$this->objAuxiliarDAO->obtenerNombrePacientePorInternado($idInternado)) {
            $this->objMensaje->mensajeSistemaShow("El ID de Internado seleccionado no es válido o no existe.", $rutaRetorno, 'systemOut', false);
            return;
        }

        // c) Validar que el ID de Médico exista
        if (!$this->objAuxiliarDAO->obtenerNombrePersonalPorIdUsuario($idMedico)) {
            $this->objMensaje->mensajeSistemaShow("El ID de Médico seleccionado no es válido o no existe.", $rutaRetorno, 'systemOut', false);
            return;
        }

        // d) Validar ID de Enfermera si no está vacío
        $idEnfermera = empty($idEnfermera) ? NULL : (int)$idEnfermera;
        if ($idEnfermera !== NULL && !$this->objAuxiliarDAO->obtenerNombrePersonalPorIdUsuario($idEnfermera)) {
            $this->objMensaje->mensajeSistemaShow("El ID de Enfermera seleccionado no es válido o no existe.", $rutaRetorno, 'systemOut', false);
            return;
        }
        
        // --- 3. Ejecución de la Acción ---
        $resultado = $this->objSeguimientoDAO->editarSeguimiento(
            $idSeguimiento, 
            $idInternado, 
            $idMedico, 
            $idEnfermera, 
            $evolucion, 
            $tratamiento
        );

        // --- 4. Manejo de Respuesta ---
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Evolución clínica actualizada correctamente.', '../indexEvolucionClinicaPacienteHospitalizado.php', 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al actualizar la evolución. Puede ser un fallo en la base de datos o que no hubo cambios que guardar.', $rutaRetorno, 'error');
        }
    }
}
?>