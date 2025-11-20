<?php

include_once('../../../../modelo/OrdenPagoDAO.php'); 

include_once('../../../../shared/mensajeSistema.php');

class controlAgregarOdenPreFactura
{
    private $objOrden;
    private $objPaciente;
    private $objCita;    // Ahora usará la clase EntidadCitas (el nombre que definimos)
    private $objInternado; // Ahora usará la clase EntidadInternados (el nombre que definimos)
    private $objMensaje;

    public function __construct()
    {
        
        // Se instancian las clases definidas dentro del archivo OrdenPagoDAO.php
        $this->objOrden = new OrdenPago();
        $this->objPaciente = new Paciente(); 
        
        // Instancia directa de las clases auxiliares que contienen las consultas
        // y que ahora están definidas en el archivo unificado.
        $this->objCita = new EntidadCitas(); 
        $this->objInternado = new EntidadInternados(); 
        
        $this->objMensaje = new mensajeSistema();
    }

    // --- Métodos para AJAX ---
    public function obtenerCitasPorPaciente($idPaciente)
    {
        // El método de la clase EntidadCitas es correcto
        return $this->objCita->obtenerCitasPendientesPorPaciente($idPaciente); 
    }
    
    public function obtenerInternadosPorPaciente($idPaciente)
    {
        // El método de la clase EntidadInternados es correcto
        return $this->objInternado->obtenerInternamientosPorPaciente($idPaciente);
    }
    // -------------------------


    public function agregarOrden($idPaciente, $idCita, $idInternado, $concepto, $monto)
    {
        $urlRetorno = '../indexOdenPrefactura.php';

        // 1. Sanitización y validación de campos obligatorios
        $idPaciente = (int)$idPaciente;
        $monto = (float)$monto;
        $idCita = empty($idCita) ? NULL : (int)$idCita;
        $idInternado = empty($idInternado) ? NULL : (int)$idInternado;

        if ($idPaciente <= 0 || empty($concepto) || $monto <= 0) {
            $this->objMensaje->mensajeSistemaShow('Faltan campos obligatorios (Paciente, Concepto, Monto) o no son válidos.', $urlRetorno, 'systemOut', false);
            return;
        }

        // 2. Validación de servicio: Debe estar asociado a Cita O Internamiento
        if ($idCita === NULL && $idInternado === NULL) {
            $this->objMensaje->mensajeSistemaShow("La orden debe estar asociada a un ID de Cita o un ID de Internamiento.", $urlRetorno, 'systemOut', false);
            return;
        }
        
        // 3. Ejecutar el registro
        $resultado = $this->objOrden->registrarOrden($idPaciente, $idCita, $idInternado, $concepto, $monto);

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Orden de Prefactura registrada correctamente con estado "Pendiente".', $urlRetorno, 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al registrar la Orden de Prefactura. Verifique los IDs de Cita/Internamiento.', $urlRetorno, 'error');
        }
    }
}