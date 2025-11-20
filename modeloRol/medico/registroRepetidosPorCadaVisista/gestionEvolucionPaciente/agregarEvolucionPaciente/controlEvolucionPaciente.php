<?php

include_once('../../../../../modelo/EvolucionPacienteDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

class controlEvolucionPaciente
{
    private $objEvolucionDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objEvolucionDAO = new EvolucionPacienteDAO();
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Procesa los datos y llama al DAO para registrar la nueva evolución.
     */
    public function registrarEvolucion($data)
    {
        // 1. Obtener y sanitizar datos
        $historiaClinicaId  = (int)($data['historia_clinica_id'] ?? 0);
        $idUsuario          = (int)($data['id_medico'] ?? 0);
        $notaSubjetiva      = trim($data['nota_subjetiva'] ?? '');
        $notaObjetiva       = trim($data['nota_objetiva'] ?? '');
        $analisis           = trim($data['analisis'] ?? '');
        $planDeAccion       = trim($data['plan_de_accion'] ?? '');

        // 2. OBTENER EL ID_MEDICO REAL desde EvolucionPacienteDAO
        $idMedico = $this->objEvolucionDAO->obtenerIdMedicoPorUsuario($idUsuario);
        
        if (!$idMedico) {
            $this->objMensaje->mensajeSistemaShow(
                'No se encontró un médico asociado a su usuario.', 
                "./formEvolucionPaciente.php", 
                'error'
            );
            return;
        }

        // 3. Validación crítica
        if ($historiaClinicaId <= 0) {
            $this->objMensaje->mensajeSistemaShow(
                'Debe seleccionar un paciente con historia clínica válida.', 
                "./formEvolucionPaciente.php", 
                'error'
            );
            return;
        }

        if (empty($notaSubjetiva) || empty($notaObjetiva) || empty($planDeAccion)) {
            $this->objMensaje->mensajeSistemaShow(
                'Los campos Subjetiva, Objetiva y Plan de Acción son obligatorios.', 
                "./formEvolucionPaciente.php?error=" . urlencode("Campos obligatorios faltantes"), 
                'error'
            );
            return;
        }

        // 4. Ejecutar el registro CON EL ID_MEDICO CORRECTO
        $resultado = $this->objEvolucionDAO->registrarEvolucion(
            $historiaClinicaId,
            $idMedico,
            $notaSubjetiva,
            $notaObjetiva,
            $analisis,
            $planDeAccion
        );

        // 5. Manejo del resultado y redirección
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                'Nota de Evolución registrada correctamente.', 
                "../indexEvolucionPaciente.php?hc_id=" . $historiaClinicaId, 
                'success'
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                'Error al registrar la evolución en la Base de Datos.', 
                "./formEvolucionPaciente.php?error=" . urlencode("Error de base de datos"), 
                'error'
            );
        }
    }
}
?>