<?php
session_start();
include_once('../../../../../../modelo/RecetaDetalleDAO.php');
include_once('../../../../../../shared/mensajeSistema.php');

class controlAgregarDetalleCita
{
    private $objDetalle;
    private $objMensaje;

    public function __construct()
    {
        $this->objDetalle = new RecetaDetalleDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function agregarDetalle($idReceta, $medicamento, $dosis, $frecuencia, $duracion = null, $notas = null)
    {
        $urlRetorno = '../indexDetalleCita.php';

        // Verificar que el usuario esté logueado y sea médico
        if (!$this->validarUsuarioMedico()) {
            $this->objMensaje->mensajeSistemaShow('Solo los médicos pueden agregar detalles de recetas.', '../../../../index.php', 'error');
            return false;
        }

        // Validación de campos obligatorios
        if (empty($idReceta) || empty($medicamento) || empty($dosis) || empty($frecuencia)) {
            $this->objMensaje->mensajeSistemaShow('Todos los campos marcados con * son obligatorios.', './indexAgregarDetalleCita.php', 'error');
            return false;
        }

        // Validación de longitud mínima
        if (strlen(trim($medicamento)) < 2) {
            $this->objMensaje->mensajeSistemaShow('El nombre del medicamento debe tener al menos 2 caracteres.', './indexAgregarDetalleCita.php', 'error');
            return false;
        }

        if (strlen(trim($dosis)) < 1) {
            $this->objMensaje->mensajeSistemaShow('La dosis es obligatoria.', './indexAgregarDetalleCita.php', 'error');
            return false;
        }

        // Verificar que la receta existe
        if (!$this->objDetalle->existeReceta($idReceta)) {
            $this->objMensaje->mensajeSistemaShow('La receta seleccionada no existe.', './indexAgregarDetalleCita.php', 'error');
            return false;
        }

        // Verificar que el médico es el dueño de la receta
        $idUsuarioMedico = $_SESSION['id_usuario'];
        $idUsuarioReceta = $this->objDetalle->obtenerIdUsuarioPorIdReceta($idReceta);
        
        if ($idUsuarioReceta != $idUsuarioMedico) {
            $this->objMensaje->mensajeSistemaShow('No puede agregar detalles a recetas de otros médicos.', './indexAgregarDetalleCita.php', 'error');
            return false;
        }

        // Sanitizar datos
        $idReceta = (int)$idReceta;
        $medicamento = trim(htmlspecialchars($medicamento));
        $dosis = trim(htmlspecialchars($dosis));
        $frecuencia = trim(htmlspecialchars($frecuencia));
        $duracion = $duracion ? trim(htmlspecialchars($duracion)) : null;
        $notas = $notas ? trim(htmlspecialchars($notas)) : null;

        // Ejecutar el registro
        $resultado = $this->objDetalle->registrarDetalle($idReceta, $medicamento, $dosis, $frecuencia, $duracion, $notas);

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                '✅ Detalle de receta agregado correctamente. ID: ' . $resultado, 
                $urlRetorno, 
                'success'
            );
            return true;
        } else {
            $this->objMensaje->mensajeSistemaShow(
                '❌ Error al agregar el detalle de receta. Verifique los datos e intente nuevamente.', 
                './indexAgregarDetalleCita.php', 
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
     * Obtiene recetas del médico logueado
     */
    public function obtenerRecetasDelMedico()
    {
        $idUsuarioMedico = $_SESSION['id_usuario'];
        $todasRecetas = $this->objDetalle->obtenerRecetasMedicas();
        $recetasDelMedico = [];

        foreach ($todasRecetas as $receta) {
            $idUsuarioReceta = $this->objDetalle->obtenerIdUsuarioPorIdReceta($receta['id_receta']);
            if ($idUsuarioReceta == $idUsuarioMedico) {
                $recetasDelMedico[] = $receta;
            }
        }

        return $recetasDelMedico;
    }
}
?>