<?php
session_start(); // ✅ AGREGAR ESTA LÍNEA
include_once('../../../../../../modelo/RecetaMedicaDAO.php');
include_once('../../../../../../shared/mensajeSistema.php');

class controlAgregarRecetaMedica
{
    private $objReceta;
    private $objMensaje;

    public function __construct()
    {
        $this->objReceta = new RecetaMedicaDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function agregarReceta($historiaClinicaId, $fecha, $indicacionesGenerales)
    {
        $urlRetorno = '../indexRecetaMedica.php';

        // Obtener el ID del médico logueado desde la sesión
        $idUsuarioMedico = $_SESSION['id_usuario'] ?? null;

        // Verificar que el usuario esté logueado y sea médico
        if (!$this->validarUsuarioMedico()) {
            $this->objMensaje->mensajeSistemaShow('Solo los médicos pueden registrar recetas.', '../../../../index.php', 'error');
            return false;
        }

        // Validación de campos obligatorios
        if (empty($historiaClinicaId) || empty($fecha) || empty($indicacionesGenerales)) {
            $this->objMensaje->mensajeSistemaShow('Todos los campos son obligatorios.', './indexAgregarRecetaMedica.php', 'error');
            return false;
        }

        // Validación de longitud mínima de indicaciones
        if (strlen(trim($indicacionesGenerales)) < 10) {
            $this->objMensaje->mensajeSistemaShow('Las indicaciones generales deben tener al menos 10 caracteres.', './indexAgregarRecetaMedica.php', 'error');
            return false;
        }

        // Validación de fecha (no puede ser futura)
        $fechaActual = date('Y-m-d');
        if ($fecha > $fechaActual) {
            $this->objMensaje->mensajeSistemaShow('La fecha no puede ser futura.', './indexAgregarRecetaMedica.php', 'error');
            return false;
        }

        // Sanitizar datos
        $historiaClinicaId = (int)$historiaClinicaId;
        $indicacionesGenerales = trim(htmlspecialchars($indicacionesGenerales));

        // Ejecutar el registro
        $resultado = $this->objReceta->registrarReceta($historiaClinicaId, $idUsuarioMedico, $fecha, $indicacionesGenerales);

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                '✅ Receta médica registrada correctamente. ID de receta: ' . $resultado, 
                $urlRetorno, 
                'success'
            );
            return true;
        } else {
            $this->objMensaje->mensajeSistemaShow(
                '❌ Error al registrar la receta médica. Verifique los datos e intente nuevamente.', 
                './indexAgregarRecetaMedica.php', 
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
}
?>