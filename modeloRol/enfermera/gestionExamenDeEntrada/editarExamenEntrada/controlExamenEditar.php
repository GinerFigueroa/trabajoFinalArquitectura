<?php
// C:\...\editarExamenEntrada\controlExamenEditar.php
include_once("../../../../modelo/ExamenClinicoDAO.php");
include_once('../../../../shared/mensajeSistema.php');

class controlExamenEditar
{
    private $objExamenDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objExamenDAO = new ExamenClinicoDAO(); 
        $this->objMensaje = new mensajeSistema();
    }

    public function editarExamen($examenId, $historiaClinicaId, $peso, $talla, $pulso, $idEnfermero)
    {
        $rutaRetorno = './indexExamenEditar.php?id=' . $examenId;
        $pulso = trim($pulso);
        
        // Log para debugging
        error_log("DEBUG: Editando examen ID: $examenId, HC: $historiaClinicaId, Peso: $peso, Talla: $talla, Pulso: $pulso, Enfermero: $idEnfermero");
        
        // --- 1. Validaciones de Campo Vacío (Obligatorias) ---
        if (empty($examenId) || empty($historiaClinicaId) || empty($peso) || empty($talla) || empty($pulso)) {
            $this->objMensaje->mensajeSistemaShow("Faltan campos obligatorios (Paciente, Peso, Talla y Pulso).", $rutaRetorno, 'error');
            return;
        }

        // --- 2. Validaciones de Formato y Límite ---
        if (!is_numeric($peso) || $peso <= 0 || $peso > 500) {
            $this->objMensaje->mensajeSistemaShow("El campo Peso debe ser un valor numérico positivo (máx 500).", $rutaRetorno, 'error');
            return;
        }
        if (!is_numeric($talla) || $talla <= 0 || $talla > 3.0) {
            $this->objMensaje->mensajeSistemaShow("El campo Talla debe ser un valor numérico positivo (máx 3.0).", $rutaRetorno, 'error');
            return;
        }
        if (strlen($pulso) > 20) {
            $this->objMensaje->mensajeSistemaShow("El campo Pulso no debe exceder los 20 caracteres.", $rutaRetorno, 'error');
            return;
        }
        
        // --- 3. Validaciones de Existencia de Entidades ---
        
        // a) Validar que el Examen exista
        $examenExistente = $this->objExamenDAO->obtenerExamenPorId($examenId);
        if (!$examenExistente) {
            $this->objMensaje->mensajeSistemaShow("El Examen a editar no existe.", $rutaRetorno, 'error');
            return;
        }

        // b) Validar que el Paciente/Historia Clínica exista
        if (!$this->objExamenDAO->obtenerNombrePacientePorHistoriaClinica($historiaClinicaId)) {
            $this->objMensaje->mensajeSistemaShow("El ID de Historia Clínica seleccionado no es válido o no existe.", $rutaRetorno, 'error');
            return;
        }
        
        // c) Validar ID de Enfermera si no está vacío
        $idEnfermero = empty($idEnfermero) ? NULL : (int)$idEnfermero; 
        if ($idEnfermero !== NULL && !$this->objExamenDAO->obtenerNombrePersonalPorIdUsuario($idEnfermero)) { 
            $this->objMensaje->mensajeSistemaShow("El ID de Enfermera/o seleccionado no es válido o no existe.", $rutaRetorno, 'error');
            return;
        }

        // --- 4. Ejecución de la Acción ---
        try {
            // CORRECCIÓN: Usar editarExamen() en lugar de actualizarExamen()
            $resultado = $this->objExamenDAO->editarExamen(
                $examenId,
                $historiaClinicaId, 
                $peso, 
                $talla, 
                $pulso, 
                $idEnfermero
            );

            // --- 5. Manejo de Respuesta ---
            if ($resultado) {
                $this->objMensaje->mensajeSistemaShow('Examen Clínico actualizado correctamente.', '../indexExamenEntrada.php', 'success');
            } else {
                $this->objMensaje->mensajeSistemaShow('Error al actualizar el examen. Verifique que los datos sean correctos.', $rutaRetorno, 'error');
            }
        } catch (Exception $e) {
            error_log("Error en editarExamen: " . $e->getMessage());
            $this->objMensaje->mensajeSistemaShow('Error interno al actualizar el examen.', $rutaRetorno, 'error');
        }
    }
}
?>