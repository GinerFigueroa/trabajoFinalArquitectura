
<?php
include_once('../../../../../modelo/OrdenExamenDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

class controlAgregarExamenClinico
{
    private $objDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objDAO = new OrdenExamenDAO();
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Registra una nueva orden de examen
     */
    public function registrarOrden($historiaClinicaId, $idUsuarioMedico, $fecha, $tipoExamen, $indicaciones, $estado)
    {
        $rutaError = "./indexAgregarExamenClinico.php";
        
        // 1. Validaciones básicas
        if (empty($historiaClinicaId) || $historiaClinicaId <= 0) {
            $this->objMensaje->mensajeSistemaShow("Historia clínica no válida.", $rutaError, "error");
            return;
        }

        if (empty($idUsuarioMedico)) {
            $this->objMensaje->mensajeSistemaShow("No se pudo identificar al médico.", $rutaError, "error");
            return;
        }

        if (empty($tipoExamen)) {
            $this->objMensaje->mensajeSistemaShow("El tipo de examen es obligatorio.", $rutaError, "error");
            return;
        }

        // 2. Verificar que el usuario es médico
        if (!$this->objDAO->esUsuarioMedico($idUsuarioMedico)) {
            $this->objMensaje->mensajeSistemaShow("El usuario no tiene permisos de médico.", $rutaError, "error");
            return;
        }

        // 3. Obtener id_medico
        $idMedico = $this->objDAO->obtenerIdMedicoPorUsuario($idUsuarioMedico);
        if (!$idMedico) {
            $this->objMensaje->mensajeSistemaShow("El médico no está registrado en la tabla de médicos.", $rutaError, "error");
            return;
        }

        // 4. Limpiar y preparar datos
        $tipoExamen = $this->limpiarTexto($tipoExamen);
        $indicaciones = $this->limpiarTexto($indicaciones);

        // 5. Validar que la historia clínica existe
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

        // 6. Intentar registrar la nueva orden
        $resultado = $this->objDAO->registrarOrden(
            $historiaClinicaId,
            $idUsuarioMedico, // Pasamos id_usuario, el DAO lo convierte a id_medico
            $fecha,
            $tipoExamen,
            $indicaciones,
            $estado
        );

        // 7. Manejar resultado
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                "Orden de examen creada correctamente.", 
                "../indexOrdenExamenClinico.php", 
                "success"
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                "Error al crear la orden de examen. Verifique que todos los datos sean correctos.", 
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