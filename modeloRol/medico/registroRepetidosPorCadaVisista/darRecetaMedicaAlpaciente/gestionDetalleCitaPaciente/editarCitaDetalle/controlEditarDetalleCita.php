<?php
session_start();
include_once('../../../../../../modelo/RecetaDetalleDAO.php');
include_once('../../../../../../shared/mensajeSistema.php');

class controlEditarDetalleCita
{
    private $objDetalle;
    private $objMensaje;

    public function __construct()
    {
        $this->objDetalle = new RecetaDetalleDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function editarDetalle($idDetalle, $medicamento, $dosis, $frecuencia, $duracion = null, $notas = null)
    {
        $urlRetorno = '../indexDetalleCita.php';

        // Verificar que el usuario esté logueado y sea médico
        if (!$this->validarUsuarioMedico()) {
            $this->objMensaje->mensajeSistemaShow('Solo los médicos pueden editar detalles de recetas.', '../../../../index.php', 'error');
            return false;
        }

        // Validación de campos obligatorios
        if (empty($idDetalle) || empty($medicamento) || empty($dosis) || empty($frecuencia)) {
            $this->objMensaje->mensajeSistemaShow('Todos los campos marcados con * son obligatorios.', './indexEditarDetalleCita.php?id=' . $idDetalle, 'error');
            return false;
        }

        // Validación de longitud mínima
        if (strlen(trim($medicamento)) < 2) {
            $this->objMensaje->mensajeSistemaShow('El nombre del medicamento debe tener al menos 2 caracteres.', './indexEditarDetalleCita.php?id=' . $idDetalle, 'error');
            return false;
        }

        if (strlen(trim($dosis)) < 1) {
            $this->objMensaje->mensajeSistemaShow('La dosis es obligatoria.', './indexEditarDetalleCita.php?id=' . $idDetalle, 'error');
            return false;
        }

        // Verificar que el detalle existe y pertenece al médico
        $idUsuarioMedico = $_SESSION['id_usuario'];
        if (!$this->objDetalle->validarPropiedadDetalle($idDetalle, $idUsuarioMedico)) {
            $this->objMensaje->mensajeSistemaShow('No tiene permisos para editar este detalle.', $urlRetorno, 'error');
            return false;
        }

        // Sanitizar datos
        $idDetalle = (int)$idDetalle;
        $medicamento = trim(htmlspecialchars($medicamento));
        $dosis = trim(htmlspecialchars($dosis));
        $frecuencia = trim(htmlspecialchars($frecuencia));
        $duracion = $duracion ? trim(htmlspecialchars($duracion)) : null;
        $notas = $notas ? trim(htmlspecialchars($notas)) : null;

        // Verificar si hay cambios reales (opcional - para optimización)
        $detalleOriginal = $this->objDetalle->obtenerDetallePorId($idDetalle);
        $sinCambios = $detalleOriginal['medicamento'] === $medicamento &&
                     $detalleOriginal['dosis'] === $dosis &&
                     $detalleOriginal['frecuencia'] === $frecuencia &&
                     $detalleOriginal['duracion'] === $duracion &&
                     $detalleOriginal['notas'] === $notas;

        if ($sinCambios) {
            $this->objMensaje->mensajeSistemaShow('No se detectaron cambios en el detalle.', './indexEditarDetalleCita.php?id=' . $idDetalle, 'info');
            return true;
        }

        // Ejecutar la actualización
        $resultado = $this->objDetalle->actualizarDetalle($idDetalle, $medicamento, $dosis, $frecuencia, $duracion, $notas);

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                '✅ Detalle de receta actualizado correctamente. ID: ' . $idDetalle, 
                $urlRetorno, 
                'success'
            );
            return true;
        } else {
            $this->objMensaje->mensajeSistemaShow(
                '❌ Error al actualizar el detalle de receta. Verifique los datos e intente nuevamente.', 
                './indexEditarDetalleCita.php?id=' . $idDetalle, 
                'error'
            );
            return false;
        }
    }

    /**
     * Valida si el usuario logueado es médico
     */
    private function validarUsuarioMedico()
    {
        return isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2 && isset($_SESSION['id_usuario']);
    }

    /**
     * Obtiene información completa del detalle para validaciones
     */
    public function obtenerDetalle($idDetalle)
    {
        return $this->objDetalle->obtenerDetallePorId($idDetalle);
    }
}
?>