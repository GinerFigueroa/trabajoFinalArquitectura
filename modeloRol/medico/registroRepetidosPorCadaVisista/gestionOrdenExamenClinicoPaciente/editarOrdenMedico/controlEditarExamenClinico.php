<?php
include_once('../../../../../modelo/OrdenExamenDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

class controlEditarExamenClinico
{
    private $objDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objDAO = new OrdenExamenDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function editarOrdenExamen($idOrden, $historiaClinicaId, $idUsuarioMedico, $fecha, $tipoExamen, $indicaciones, $estado, $resultados)
    {
        $rutaRetorno = "./indexEditarExamenClinico.php?id_orden=" . $idOrden;
        
        // 1. Validaciones básicas de campos obligatorios
        if (empty($historiaClinicaId) || empty($fecha) || empty($tipoExamen) || empty($estado)) {
            $this->objMensaje->mensajeSistemaShow(
                "Todos los campos obligatorios deben ser completados.", 
                $rutaRetorno, 
                'error'
            );
            return;
        }

        // 2. Validar que la orden existe
        $ordenExistente = $this->objDAO->obtenerOrdenPorId($idOrden);
        if (!$ordenExistente) {
            $this->objMensaje->mensajeSistemaShow(
                "La orden de examen no existe o ha sido eliminada.", 
                "../indexOrdenExamenClinico.php", 
                'error'
            );
            return;
        }

        // 3. Validar que el médico de sesión puede editar esta orden
        // (Opcional: verificar que el médico es el dueño de la orden)
        $idMedicoOrden = $this->objDAO->obtenerIdMedicoPorOrden($idOrden);
        $idMedicoSesion = $this->objDAO->obtenerIdMedicoPorUsuario($idUsuarioMedico);
        
        if ($idMedicoOrden != $idMedicoSesion) {
            $this->objMensaje->mensajeSistemaShow(
                "No tiene permisos para editar esta orden.", 
                "../indexOrdenExamenClinico.php", 
                'error'
            );
            return;
        }

        // 4. Validar formato de fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $this->objMensaje->mensajeSistemaShow(
                "Formato de fecha inválido.", 
                $rutaRetorno, 
                'error'
            );
            return;
        }

        // 5. Validar longitud de campos de texto
        if (strlen($tipoExamen) > 100) {
            $this->objMensaje->mensajeSistemaShow(
                "El tipo de examen no puede exceder los 100 caracteres.", 
                $rutaRetorno, 
                'error'
            );
            return;
        }

        // 6. Usar el mismo médico (no cambiar)
        $idMedico = $idMedicoSesion;

        // 7. Actualizar la orden en la base de datos
        $resultado = $this->objDAO->actualizarOrden(
            $idOrden,
            $historiaClinicaId,
            $idMedico, // Mantener el mismo médico
            $fecha,
            $tipoExamen,
            $indicaciones,
            $estado,
            $resultados
        );

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                "Orden de examen actualizada correctamente.", 
                "../indexOrdenExamenClinico.php", 
                'success'
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                "Error al actualizar la orden de examen. Por favor, intente nuevamente.", 
                $rutaRetorno, 
                'error'
            );
        }
    }
}