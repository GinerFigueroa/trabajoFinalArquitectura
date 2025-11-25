<?php
// Archivo: controlInternadoPDF.php

session_start();

// Dependencias
require_once('../../../../../dompdf/autoload.inc.php');
use Dompdf\Dompdf;
include_once("../../../../../modelo/InternadoPDFseguimientoDAO.php");
include_once("../../../../../shared/mensajeSistema.php");

// Incluimos el archivo que definirá la interfaz Command y las clases concretas
include_once('./comando/ComandoGenerarPDF.php'); 


/**
 * Clase controlInternadoPDF:
 * - Rol MVC: Controlador (Action Controller).
 * - Rol Patrones: Cliente del Comando y Factory Method (crea la instancia del Command).
 */
class controlInternadoPDF
{
    // Atributos
    private $objMensaje;

    public function __construct() {
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Factory Method y Procesador Principal.
     * @return void
     */
    public function procesarSolicitud()
    {
        $action = $_GET['action'] ?? '';
        $idInternado = $_GET['id'] ?? null;

        if (!$idInternado || !is_numeric($idInternado)) {
            $this->objMensaje->mensajeSistemaShow("ID de internado no válido.", "../indexGestionInternados.php", "error");
            return;
        }

        // 1. Factory Method: Determina qué Comando concreto crear.
        // El 'Completo' es el único implementado aquí, los otros serían clases similares.
        switch ($action) {
            case 'completo':
                // Atributo: $comando, Metodo: __construct (inicializa Command)
                $comando = new PDFCompletoCommand($idInternado);
                break;
            case 'resumen':
                // Ejemplo de otro Command: $comando = new PDFResumenCommand($idInternado);
                $this->objMensaje->mensajeSistemaShow("Opción 'resumen' no implementada. Ejecutando Completo.", "../indexGestionInternados.php", "error");
                $comando = new PDFCompletoCommand($idInternado); 
                break;
            default:
                $this->objMensaje->mensajeSistemaShow("Acción no válida.", "../indexGestionInternados.php", "error");
                return;
        }

        // 2. Cliente del Command: Ejecución del Comando
        // Metodo: ejecutar (llamado al Command)
        $comando->ejecutar();
    }
}

// Ejecutar el controlador
$objControl = new controlInternadoPDF();
$objControl->procesarSolicitud();