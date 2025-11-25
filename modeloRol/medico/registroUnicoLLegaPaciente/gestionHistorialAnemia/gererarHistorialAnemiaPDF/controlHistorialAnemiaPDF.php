<?php

include_once('../../../../../modelo/HistorialAnemiaPacienteDAO.php');
include_once('../../../../../shared/mensajeSistema.php');
include_once('./formHistorialAnemiaPDF.php');

class controlHistorialAnemiaPDF
{
    // Atributos: Dependencias (Modelo y Vistas)
    // Atributo: `$objHistorial` (Modelo / DAO)
    private $objHistorial;
    // Atributo: `$objMensaje` (Componente compartido)
    private $objMensaje;
    // Atributo: `$objFormPDF` (Vista PDF)
    private $objFormPDF;

    // Método: Constructor
    public function __construct()
    {
        $this->objHistorial = new HistorialAnemiaPacienteDAO();
        $this->objMensaje = new mensajeSistema();
        $this->objFormPDF = new formHistorialAnemiaPDF();
    }

    // Método: `generarPDF` (Método principal del Controlador)
    public function generarPDF()
    {
        // Obtención de datos del Request
        // Atributo: `$idAnamnesis`
        $idAnamnesis = $_GET['id'] ?? null;
        
        // Validación de entrada
        if (empty($idAnamnesis) || !is_numeric($idAnamnesis)) {
            $this->objMensaje->mensajeSistemaShow(
                "ID de historial no proporcionado o no válido.", 
                "../indexHistorialAnemia.php", 
                "error"
            );
            return;
        }

        // 1. Interacción con el Modelo (DAO)
        // Método: `obtenerHistorialPorId`
        $historial = $this->objHistorial->obtenerHistorialPorId($idAnamnesis);

        if (!$historial) {
            $this->objMensaje->mensajeSistemaShow(
                "Historial de anemia no encontrado.", 
                "../indexHistorialAnemia.php", 
                "error"
            );
            return;
        }

        // 2. Interacción con la Vista (Muestra el resultado)
        // Método: `generarPDFShow`
        $this->objFormPDF->generarPDFShow($historial);
    }
}
?>