<?php
// controlAgregarHistorialPaciente.php

include_once('../../../../../modelo/RegistroMedicoDAO.php'); 
include_once('../../../../../shared/mensajeSistema.php'); 

class controlAgregarHistorialPaciente
{
    private $objDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objDAO = new RegistroMedicoDAO();
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Registra un nuevo registro médico
     */
    public function registrarRegistro($historiaClinicaId, $motivoConsulta, $enfermedadActual, $tiempoEnfermedad, $signosSintomas, $riesgos, $motivoUltimaVisita, $ultimaVisitaMedica)
    {
        // Ruta para redirección en caso de error
        $rutaError = "../indexHistorialMedico.php";
        
        // 1. Validaciones básicas
        if (empty($historiaClinicaId) || $historiaClinicaId <= 0) {
            $this->objMensaje->mensajeSistemaShow("Historia clínica no válida.", $rutaError, "error");
            return;
        }

        if (empty($motivoConsulta)) {
            $this->objMensaje->mensajeSistemaShow("El motivo de consulta es obligatorio.", $rutaError, "error");
            return;
        }

        // 2. Limpiar y preparar datos
        $motivoConsulta = $this->limpiarTexto($motivoConsulta);
        $enfermedadActual = $this->limpiarTexto($enfermedadActual);
        $tiempoEnfermedad = $this->limpiarTexto($tiempoEnfermedad);
        $signosSintomas = $this->limpiarTexto($signosSintomas);
        $riesgos = $this->limpiarTexto($riesgos);
        $motivoUltimaVisita = $this->limpiarTexto($motivoUltimaVisita);

        // 3. Validar fecha si se proporcionó
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

        // 4. Verificar que la historia clínica existe
        $historias = $this->objDAO->obtenerHistoriasClinicas();
        $historiaExiste = false;
        foreach ($historias as $historia) {
            if ($historia['historia_clinica_id'] == $historiaClinicaId) {
                $historiaExiste = true;
                break;
            }
        }

        if (!$historiaExiste) {
            $this->objMensaje->mensajeSistemaShow("La historia clínica seleccionada no existe.", $rutaError, "error");
            return;
        }

        // 5. Intentar registrar el nuevo registro
        $resultado = $this->objDAO->registrarRegistro(
            $historiaClinicaId,
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
                "Registro médico creado correctamente.", 
                "../indexHistorialMedico.php", 
                "success"
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                "Error al crear el registro médico. Por favor, intente nuevamente.", 
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