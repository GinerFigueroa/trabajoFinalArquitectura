<?php
session_start();
include_once('../../../../../../modelo/RecetaDetalleDAO.php');
include_once('../../../../../../modelo/RecetaMedicaDAO.php');
include_once('../../../../../../shared/mensajeSistema.php');
include_once('./formCitaMedicaPDF.php');

class controlCitaMedicaPDF
{
    private $objDetalle;
    private $objReceta;
    private $objMensaje;
    private $objFormPDF;

    public function __construct()
    {
        $this->objDetalle = new RecetaDetalleDAO();
        $this->objReceta = new RecetaMedicaDAO();
        $this->objMensaje = new mensajeSistema();
        $this->objFormPDF = new formCitaMedicaPDF();
    }

    public function generarPDF()
    {
        // Verificar que el usuario tenga sesión activa
        if (!isset($_SESSION['login'])) {
            $this->objMensaje->mensajeSistemaShow(
                '❌ Acceso denegado. Debe iniciar sesión para generar PDF.', 
                '../../../../index.php', 
                'error'
            );
            exit();
        }

        // Obtener ID de la receta
        $idReceta = $_GET['id'] ?? null;
        
        if (empty($idReceta) || !is_numeric($idReceta)) {
            $this->objMensaje->mensajeSistemaShow(
                "❌ ID de receta no proporcionado o no válido.", 
                "../indexDetalleCita.php", 
                "error"
            );
            exit();
        }

        // Obtener información de la receta
        $receta = $this->objReceta->obtenerRecetaPorId($idReceta);

        if (!$receta) {
            $this->objMensaje->mensajeSistemaShow(
                "❌ Receta médica no encontrada.", 
                "../indexDetalleCita.php", 
                "error"
            );
            exit();
        }

        // Obtener detalles de la receta
        $detalles = $this->objDetalle->obtenerDetallesPorReceta($idReceta);

        // Verificar permisos para ver la receta
        if (!$this->tienePermisosParaVerReceta($receta)) {
            $this->objMensaje->mensajeSistemaShow(
                "❌ No tiene permisos para ver esta receta médica.", 
                "../indexDetalleCita.php", 
                "error"
            );
            exit();
        }

        // Determinar el tipo de PDF a generar (completo o simple)
        $tipoPDF = $_GET['tipo'] ?? 'completo';

        try {
            if ($tipoPDF === 'simple') {
                $this->objFormPDF->generarPDFSimple($receta, $detalles);
            } else {
                $this->objFormPDF->generarPDFShow($receta, $detalles);
            }
        } catch (Exception $e) {
            error_log("Error generando PDF de receta: " . $e->getMessage());
            $this->objMensaje->mensajeSistemaShow(
                "❌ Error al generar el PDF: " . $e->getMessage(), 
                "../indexDetalleCita.php", 
                "error"
            );
        }
    }

    /**
     * Verifica si el usuario tiene permisos para ver la receta
     */
    private function tienePermisosParaVerReceta($receta)
    {
        $rolUsuario = $_SESSION['rol_id'] ?? null;
        $idUsuario = $_SESSION['id_usuario'] ?? null;

        // Administradores pueden ver todas las recetas
        if ($rolUsuario == 1) {
            return true;
        }

        // Médicos solo pueden ver sus propias recetas
        if ($rolUsuario == 2) {
            $idMedicoReceta = $receta['id_medico'];
            $idUsuarioReceta = $this->objReceta->obtenerIdUsuarioPorIdMedico($idMedicoReceta);
            return $idUsuarioReceta == $idUsuario;
        }

        // Pacientes solo pueden ver sus propias recetas
        if ($rolUsuario == 4) {
            $idPacienteReceta = $this->obtenerIdPacienteDeReceta($receta['historia_clinica_id']);
            return $idPacienteReceta == $idUsuario;
        }

        return false;
    }

    /**
     * Obtiene el ID del paciente desde la historia clínica
     */
    private function obtenerIdPacienteDeReceta($historiaClinicaId)
    {
        $sql = "SELECT id_paciente FROM historia_clinica WHERE historia_clinica_id = ?";
        
        $stmt = $this->objReceta->connection->prepare($sql);
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param("i", $historiaClinicaId);
        
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }
        
        $resultado = $stmt->get_result();
        
        if ($fila = $resultado->fetch_assoc()) {
            $idPaciente = $fila['id_paciente'];
            $stmt->close();
            return $idPaciente;
        }
        
        $stmt->close();
        return null;
    }

    /**
     * Método para descargar el PDF (opcional)
     */
    public function descargarPDF($idReceta)
    {
        $receta = $this->objReceta->obtenerRecetaPorId($idReceta);
        $detalles = $this->objDetalle->obtenerDetallesPorReceta($idReceta);
        
        if ($receta && $this->tienePermisosParaVerReceta($receta)) {
            // Configurar headers para descarga
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="Receta-Medica-' . $idReceta . '.pdf"');
            
            $this->objFormPDF->generarPDFShow($receta, $detalles);
        } else {
            $this->objMensaje->mensajeSistemaShow(
                "❌ No tiene permisos para descargar esta receta.", 
                "../indexDetalleCita.php", 
                "error"
            );
        }
    }
}
?>