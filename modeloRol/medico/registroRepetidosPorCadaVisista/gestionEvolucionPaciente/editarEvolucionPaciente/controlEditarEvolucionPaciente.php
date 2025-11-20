<?php
// controlEditarEvolucionPaciente.php

include_once('../../../../../modelo/EvolucionPacienteDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

class controlEditarEvolucionPaciente
{
    private $objDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objDAO = new EvolucionPacienteDAO();
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Edita una evolución médica existente
     */
    public function editarEvolucion($idEvolucion, $notaSubjetiva, $notaObjetiva, $analisis, $planDeAccion)
    {
        // Ruta para redirección en caso de error
        $rutaError = "../editarEvolucionPaciente/indexEvolucionPaciente.php?evo_id=" . $idEvolucion;
        
        // 1. Validaciones básicas
        if (empty($idEvolucion) || $idEvolucion <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de evolución no válido.", "../indexEvolucionPaciente.php", "error");
            return;
        }

        if (empty($notaSubjetiva)) {
            $this->objMensaje->mensajeSistemaShow("La nota subjetiva (S) es obligatoria.", $rutaError, "error");
            return;
        }

        // 2. Verificar que la evolución existe
        $evolucionExistente = $this->objDAO->obtenerEvolucionPorId($idEvolucion);
        if (!$evolucionExistente) {
            $this->objMensaje->mensajeSistemaShow("La evolución médica no existe o no se puede encontrar.", "../indexEvolucionPaciente.php", "error");
            return;
        }

        // 3. Limpiar y preparar datos
        $notaSubjetiva = $this->limpiarTexto($notaSubjetiva);
        $notaObjetiva = $this->limpiarTexto($notaObjetiva);
        $analisis = $this->limpiarTexto($analisis);
        $planDeAccion = $this->limpiarTexto($planDeAccion);

        // 4. Intentar actualizar la evolución
        $resultado = $this->objDAO->editarEvolucion(
            $idEvolucion,
            $notaSubjetiva,
            $notaObjetiva,
            $analisis,
            $planDeAccion
        );

        // 5. Manejar resultado
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                "Evolución médica actualizada correctamente.", 
                "../indexEvolucionPaciente.php", 
                "success"
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                "Error al actualizar la evolución médica. Por favor, intente nuevamente.", 
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
}
?>