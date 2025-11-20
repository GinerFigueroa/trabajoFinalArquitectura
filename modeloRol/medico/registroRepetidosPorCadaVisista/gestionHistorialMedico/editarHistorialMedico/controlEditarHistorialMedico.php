<?php


include_once('../../../../../modelo/RegistroMedicoDAO.php'); 
include_once('../../../../../shared/mensajeSistema.php'); 

class controlEditarHistorialPaciente
{
    private $objDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objDAO = new RegistroMedicoDAO();
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Edita un registro médico existente
     */
    public function editarRegistro($idRegistro, $motivoConsulta, $enfermedadActual, $tiempoEnfermedad, $signosSintomas, $riesgos, $motivoUltimaVisita, $ultimaVisitaMedica)
    {
        // Ruta para redirección en caso de error
        $rutaError = "../indexHistorialMedico.php?reg_id=" . $idRegistro;
        
        // 1. Validaciones básicas
        if (empty($idRegistro) || $idRegistro <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de registro no válido.", "../indexHistorialMedico.php", "error");
            return;
        }

        if (empty($motivoConsulta)) {
            $this->objMensaje->mensajeSistemaShow("El motivo de consulta es obligatorio.", $rutaError, "error");
            return;
        }

        // 2. Verificar que el registro existe
        $registroExistente = $this->objDAO->obtenerRegistroPorId($idRegistro);
        if (!$registroExistente) {
            $this->objMensaje->mensajeSistemaShow("El registro médico no existe o no se puede encontrar.", "../indexHistorialMedico.php", "error");
            return;
        }

        // 3. Limpiar y preparar datos
        $motivoConsulta = $this->limpiarTexto($motivoConsulta);
        $enfermedadActual = $this->limpiarTexto($enfermedadActual);
        $tiempoEnfermedad = $this->limpiarTexto($tiempoEnfermedad);
        $signosSintomas = $this->limpiarTexto($signosSintomas);
        $riesgos = $this->limpiarTexto($riesgos);
        $motivoUltimaVisita = $this->limpiarTexto($motivoUltimaVisita);

        // 4. Validar fecha si se proporcionó
        if ($ultimaVisitaMedica) {
            if (!$this->validarFecha($ultimaVisitaMedica)) {
                $this->objMensaje->mensajeSistemaShow("La fecha de última visita médica no es válida.", $rutaError, "error");
                return;
            }
            
            // Asegurar que la fecha no sea futura
            if (strtotime($ultimaVisitaMedica) > time()) {
                $this->objMensaje->mensajeSistemaShow("La fecha de última visita médica no puede ser futura.", $rutaError, "error");
                return;
            }
        }

        // 5. Intentar actualizar el registro
        $resultado = $this->objDAO->editarRegistro(
            $idRegistro,
            $riesgos,
            $motivoConsulta,
            $enfermedadActual,
            $tiempoEnfermedad,
            $signosSintomas,
            $motivoUltimaVisita,
            $ultimaVisitaMedica
        );

        // 6. Manejar resultado
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                "Registro médico actualizado correctamente.", 
                "../indexHistorialMedico.php", 
                "success"
                
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                "Error al actualizar el registro médico. Por favor, intente nuevamente.", 
                $rutaError, 
                "error"
            );
        }
    }

    /**
     * Método auxiliar para limpiar texto
     */
    private function limpiarTexto($texto)
    {
        return trim(htmlspecialchars($texto));
    }

    /**
     * Método auxiliar para validar fecha
     */
    private function validarFecha($fecha)
    {
        $patron = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($patron, $fecha)) {
            return false;
        }
        
        list($año, $mes, $dia) = explode('-', $fecha);
        return checkdate($mes, $dia, $año);
    }
}
?>