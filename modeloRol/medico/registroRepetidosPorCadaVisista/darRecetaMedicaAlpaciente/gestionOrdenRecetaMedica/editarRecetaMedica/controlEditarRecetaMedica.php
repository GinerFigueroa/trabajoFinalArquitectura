<?php
session_start();
include_once('../../../../../../modelo/RecetaMedicaDAO.php');
include_once('../../../../../../shared/mensajeSistema.php');

class controlEditarRecetaMedica
{
    private $objReceta;
    private $objMensaje;

    public function __construct()
    {
        $this->objReceta = new RecetaMedicaDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function editarReceta($idReceta, $historiaClinicaId, $fecha, $indicacionesGenerales)
    {
        $urlRetorno = '../indexRecetaMedica.php';

        // Verificar que el usuario esté logueado y sea médico
        if (!$this->validarUsuarioMedico()) {
            $this->objMensaje->mensajeSistemaShow('Solo los médicos pueden editar recetas.', '../../../../index.php', 'error');
            return false;
        }

        // Validación de campos obligatorios
        if (empty($idReceta) || empty($historiaClinicaId) || empty($fecha) || empty($indicacionesGenerales)) {
            $this->objMensaje->mensajeSistemaShow('Todos los campos son obligatorios.', './indexEditarRecetaMedica.php?id=' . $idReceta, 'error');
            return false;
        }

        // Validación de longitud mínima de indicaciones
        if (strlen(trim($indicacionesGenerales)) < 10) {
            $this->objMensaje->mensajeSistemaShow('Las indicaciones generales deben tener al menos 10 caracteres.', './indexEditarRecetaMedica.php?id=' . $idReceta, 'error');
            return false;
        }

        // Validación de fecha (no puede ser futura)
        $fechaActual = date('Y-m-d');
        if ($fecha > $fechaActual) {
            $this->objMensaje->mensajeSistemaShow('La fecha no puede ser futura.', './indexEditarRecetaMedica.php?id=' . $idReceta, 'error');
            return false;
        }

        // Verificar que la receta existe y pertenece al médico logueado
        if (!$this->validarPropiedadReceta($idReceta)) {
            $this->objMensaje->mensajeSistemaShow('No tiene permisos para editar esta receta.', $urlRetorno, 'error');
            return false;
        }

        // Sanitizar datos
        $idReceta = (int)$idReceta;
        $historiaClinicaId = (int)$historiaClinicaId;
        $indicacionesGenerales = trim(htmlspecialchars($indicacionesGenerales));

        // Obtener el id_medico del usuario logueado
        $idUsuarioMedico = $_SESSION['id_usuario'];
        $idMedico = $this->objReceta->obtenerIdMedicoPorUsuario($idUsuarioMedico);

        if (!$idMedico) {
            $this->objMensaje->mensajeSistemaShow('Error: No se pudo identificar al médico.', './indexEditarRecetaMedica.php?id=' . $idReceta, 'error');
            return false;
        }

        // Ejecutar la actualización
        $resultado = $this->objReceta->actualizarReceta($idReceta, $historiaClinicaId, $idMedico, $fecha, $indicacionesGenerales);

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                '✅ Receta médica actualizada correctamente. ID: ' . $idReceta, 
                $urlRetorno, 
                'success'
            );
            return true;
        } else {
            $this->objMensaje->mensajeSistemaShow(
                '❌ Error al actualizar la receta médica. Verifique los datos e intente nuevamente.', 
                './indexEditarRecetaMedica.php?id=' . $idReceta, 
                'error'
            );
            return false;
        }
    }

    /**
     * Valida si la historia clínica existe
     */
    public function validarHistoriaClinica($historiaClinicaId)
    {
        $historias = $this->objReceta->obtenerHistoriasClinicas();
        foreach ($historias as $historia) {
            if ($historia['historia_clinica_id'] == $historiaClinicaId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Valida si el usuario logueado es médico
     */
    public function validarUsuarioMedico()
    {
        return isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2 && isset($_SESSION['id_usuario']);
    }

    /**
     * Valida que la receta pertenezca al médico logueado
     */
    public function validarPropiedadReceta($idReceta)
    {
        $receta = $this->objReceta->obtenerRecetaPorId($idReceta);
        
        if (!$receta) {
            return false;
        }

        $idUsuarioMedico = $_SESSION['id_usuario'];
        $idMedicoReceta = $receta['id_medico'];
        
        // Obtener el id_usuario del médico de la receta
        $idUsuarioReceta = $this->objReceta->obtenerIdUsuarioPorIdMedico($idMedicoReceta);
        
        return $idUsuarioReceta == $idUsuarioMedico;
    }

    /**
     * Obtiene información de la receta para validaciones
     */
    public function obtenerReceta($idReceta)
    {
        return $this->objReceta->obtenerRecetaPorId($idReceta);
    }
}
?>