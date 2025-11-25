<?php
// Directorio: /controlador/historialAnemia/controlEditarHistorialAnemia.php

include_once('../../../../../modelo/HistorialAnemiaPacienteDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

/**
 * Patrón: MEDIATOR (Mediador) 🤝
 * Esta clase centraliza la lógica de validación y prepara los datos 
 * antes de interactuar con el Modelo (DAO).
 */
class controlEditarHistorialAnemia
{
    // Atributo: `$objHistorial` (Modelo / DAO)
    private $objHistorial;
    // Atributo: `$objMensaje` (Componente compartido)
    private $objMensaje;

    // Método: Constructor
    public function __construct()
    {
        $this->objHistorial = new HistorialAnemiaPacienteDAO();
        $this->objMensaje = new mensajeSistema();
    }

    // Método: `procesarEdicion` (Método principal invocado por el Comando)
    public function procesarEdicion(array $datosForm): void
    {
        $urlRetorno = '../indexHistorialAnemia.php';
        $idAnamnesis = (int)($datosForm['anamnesis_id'] ?? 0);
        $urlFormulario = './indexEditarHistorialAnemia.php?id=' . $idAnamnesis;

        // 1. Validaciones del Mediador
        if (!$this->validarId($idAnamnesis, $urlRetorno)) return;
        if (!$this->validarExistencia($idAnamnesis, $urlRetorno)) return;
        if (!$this->validarEmbarazo($datosForm, $urlFormulario)) return;

        // 2. Preparación de datos (Lógica centralizada del Mediador)
        $datosActualizados = $this->prepararDatos($datosForm);

        // 3. Ejecutar la acción de negocio (Interactuar con el Modelo)
        $this->ejecutarActualizacion($idAnamnesis, $datosActualizados, $urlFormulario, $urlRetorno);
    }

    // Método: `validarId`
    private function validarId(int $idAnamnesis, string $urlRetorno): bool
    {
        if ($idAnamnesis <= 0) {
            $this->objMensaje->mensajeSistemaShow('ID de historial no válido.', $urlRetorno, 'error');
            return false;
        }
        return true;
    }

    // Método: `validarExistencia`
    private function validarExistencia(int $idAnamnesis, string $urlRetorno): bool
    {
        // Método: `obtenerHistorialPorId` (Consulta al Modelo)
        $historialExistente = $this->objHistorial->obtenerHistorialPorId($idAnamnesis);
        if (!$historialExistente) {
            $this->objMensaje->mensajeSistemaShow('El historial no existe o fue eliminado.', $urlRetorno, 'error');
            return false;
        }
        return true;
    }

    // Método: `validarEmbarazo`
    private function validarEmbarazo(array $datosForm, string $urlFormulario): bool
    {
        // Atributo: `esta_embarazada`
        if (isset($datosForm['esta_embarazada']) && $datosForm['esta_embarazada'] == '1') {
            $semanas = $datosForm['semanas_embarazo'] ?? null;
            // Atributo: `semanas_embarazo`
            if (empty($semanas) || !is_numeric($semanas) || $semanas < 1 || $semanas > 42) {
                $this->objMensaje->mensajeSistemaShow(
                    'Si la paciente está embarazada, debe especificar las semanas de gestación (1-42).', 
                    $urlFormulario, 
                    'error'
                );
                return false;
            }
        }
        return true;
    }

    // Método: `prepararDatos`
    private function prepararDatos(array $datosForm): array
    {
        // Lógica de mapeo y limpieza
        $datosHistorial = [
            'alergias' => trim($datosForm['alergias'] ?? ''),
            'enfermedades_pulmonares' => trim($datosForm['enfermedades_pulmonares'] ?? ''),
            // ... (resto de campos de enfermedades) ...
            'medicacion' => trim($datosForm['medicacion'] ?? ''),
            'ha_sido_operado' => trim($datosForm['ha_sido_operado'] ?? ''),
            // Atributos booleanos (conversión a 1 o 0)
            'ha_tenido_tumor' => isset($datosForm['ha_tenido_tumor']) ? 1 : 0,
            'ha_tenido_hemorragia' => isset($datosForm['ha_tenido_hemorragia']) ? 1 : 0,
            'fuma' => isset($datosForm['fuma']) ? 1 : 0,
            'frecuencia_fuma' => (isset($datosForm['fuma']) && $datosForm['fuma']) ? trim($datosForm['frecuencia_fuma'] ?? '') : null,
            'toma_anticonceptivos' => isset($datosForm['toma_anticonceptivos']) ? 1 : 0,
            'esta_embarazada' => isset($datosForm['esta_embarazada']) ? 1 : 0,
            'semanas_embarazo' => (isset($datosForm['esta_embarazada']) && $datosForm['esta_embarazada']) ? (int)($datosForm['semanas_embarazo'] ?? 0) : null,
            'periodo_lactancia' => isset($datosForm['periodo_lactancia']) ? 1 : 0
        ];

        // Limpiar campos vacíos (convertir '' a NULL para la base de datos)
        foreach ($datosHistorial as $key => $value) {
            if ($value === '') {
                $datosHistorial[$key] = null;
            }
        }

        return $datosHistorial;
    }

    // Método: `ejecutarActualizacion`
    private function ejecutarActualizacion(int $idAnamnesis, array $datos, string $urlError, string $urlExito): void
    {
        // Método: `actualizarHistorial` (Ejecución de la operación de negocio)
        $resultado = $this->objHistorial->actualizarHistorial($idAnamnesis, $datos);

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                'Historial de anemia actualizado correctamente.', 
                $urlExito, 
                'success'
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                'Error al actualizar el historial de anemia. Por favor, intente nuevamente.', 
                $urlError, 
                'error'
            );
        }
    }
}
?>