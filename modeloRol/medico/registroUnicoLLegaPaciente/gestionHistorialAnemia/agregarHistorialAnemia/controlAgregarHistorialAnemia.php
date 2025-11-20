<?php
include_once('../../../../../modelo/HistorialAnemiaPacienteDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

class controlAgregarHistorialAnemia
{
    private $objHistorial;
    private $objMensaje;

    public function __construct()
    {
        $this->objHistorial = new HistorialAnemiaPacienteDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function agregarHistorial($datosForm)
    {
        $urlRetorno = '../indexHistorialAnemia.php';
        $urlFormulario = './indexAgregarHistorialAnemia.php';

        // 1. Validar campos obligatorios
        if (empty($datosForm['historia_clinica_id']) || !is_numeric($datosForm['historia_clinica_id'])) {
            $this->objMensaje->mensajeSistemaShow('Debe seleccionar una historia clínica válida.', $urlFormulario, 'error');
            return;
        }

        $historiaClinicaId = (int)$datosForm['historia_clinica_id'];

        // 2. Verificar que la historia clínica no tenga ya un historial
        if ($this->objHistorial->existeHistorialParaHistoriaClinica($historiaClinicaId)) {
            $this->objMensaje->mensajeSistemaShow(
                'Esta historia clínica ya tiene un historial de anemia registrado.', 
                $urlFormulario, 
                'error'
            );
            return;
        }

        // 3. Validar datos específicos
        if (isset($datosForm['esta_embarazada']) && $datosForm['esta_embarazada'] == '1') {
            if (empty($datosForm['semanas_embarazo']) || !is_numeric($datosForm['semanas_embarazo']) || 
                $datosForm['semanas_embarazo'] < 1 || $datosForm['semanas_embarazo'] > 42) {
                $this->objMensaje->mensajeSistemaShow(
                    'Si la paciente está embarazada, debe especificar las semanas de gestación (1-42).', 
                    $urlFormulario, 
                    'error'
                );
                return;
            }
        }

        // 4. Preparar datos para inserción
        $datosHistorial = [
            'alergias' => trim($datosForm['alergias'] ?? ''),
            'enfermedades_pulmonares' => trim($datosForm['enfermedades_pulmonares'] ?? ''),
            'enfermedades_cardiacas' => trim($datosForm['enfermedades_cardiacas'] ?? ''),
            'enfermedades_neurologicas' => trim($datosForm['enfermedades_neurologicas'] ?? ''),
            'enfermedades_hepaticas' => trim($datosForm['enfermedades_hepaticas'] ?? ''),
            'enfermedades_renales' => trim($datosForm['enfermedades_renales'] ?? ''),
            'enfermedades_endocrinas' => trim($datosForm['enfermedades_endocrinas'] ?? ''),
            'otras_enfermedades' => trim($datosForm['otras_enfermedades'] ?? ''),
            'medicacion' => trim($datosForm['medicacion'] ?? ''),
            'ha_sido_operado' => trim($datosForm['ha_sido_operado'] ?? ''),
            'ha_tenido_tumor' => isset($datosForm['ha_tenido_tumor']) ? 1 : 0,
            'ha_tenido_hemorragia' => isset($datosForm['ha_tenido_hemorragia']) ? 1 : 0,
            'fuma' => isset($datosForm['fuma']) ? 1 : 0,
            'frecuencia_fuma' => isset($datosForm['fuma']) ? trim($datosForm['frecuencia_fuma'] ?? '') : '',
            'toma_anticonceptivos' => isset($datosForm['toma_anticonceptivos']) ? 1 : 0,
            'esta_embarazada' => isset($datosForm['esta_embarazada']) ? 1 : 0,
            'semanas_embarazo' => isset($datosForm['esta_embarazada']) ? (int)($datosForm['semanas_embarazo'] ?? 0) : null,
            'periodo_lactancia' => isset($datosForm['periodo_lactancia']) ? 1 : 0
        ];

        // 5. Limpiar campos vacíos (convertir a NULL)
        foreach ($datosHistorial as $key => $value) {
            if ($value === '') {
                $datosHistorial[$key] = null;
            }
        }

        // 6. Ejecutar inserción
        $resultado = $this->objHistorial->registrarHistorial($historiaClinicaId, $datosHistorial);

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                'Historial de anemia registrado correctamente.', 
                $urlRetorno, 
                'success'
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                'Error al registrar el historial de anemia. Por favor, intente nuevamente.', 
                $urlFormulario, 
                'error'
            );
        }
    }
}