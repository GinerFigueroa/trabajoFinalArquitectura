<?php
include_once('getEmitirBoletaInternado.php');

$controlador = new controlBoletaInternadoPDF();
$controlador->generarBoletaInternado();
?>

<?php
include_once('../../../../modelo/FacturacionInternadoDAO.php'); 
include_once('../../../../shared/mensajeSistema.php'); 
include_once('./formEmitirBoletaInternado.php'); 

class controlBoletaInternadoPDF
{
    private $objFacturaDAO;
    private $objMensaje;
    private $objFormPDF;

    public function __construct()
    {
        $this->objFacturaDAO = new FacturacionInternadoDAO(); 
        $this->objMensaje = new mensajeSistema();
        $this->objFormPDF = new formBoletaInternadoPDF();
    }

   public function generarBoletaInternado()
{
    $idBoleta = $_GET['id_Factura'] ?? null;
    $urlRedireccion = "../indexFacturacionInternadoPDF.php";

    if (empty($idBoleta) || !is_numeric($idBoleta)) {
        $this->objMensaje->mensajeSistemaShow("ID de Boleta no proporcionado o no válido.", $urlRedireccion, "error");
        return;
    }

    $Boleta = $this->objFacturaDAO->obtenerBoletaCompletaParaPDF($idBoleta);

    if (!$Boleta) {
        $this->objMensaje->mensajeSistemaShow("La Factura N° {$idBoleta} de Internado no fue encontrada.", $urlRedireccion, "error");
        return;
    }

    $this->objFormPDF->generarPDFShow($Boleta);
}
}
?>

<?php

require_once('../../../../dompdf/autoload.inc.php'); 
use Dompdf\Dompdf;
use Dompdf\Options;

class formBoletaInternadoPDF
{
    
    public function generarPDFShow($Boleta)
    {
        // 1. Crear el HTML
        $html = $this->generarHtmlFactura($Boleta);

        // 2. Configurar y renderizar Dompdf
        $options = new Options();
        // Cambiamos la fuente para simular ticket
        $options->set('defaultFont', 'Courier'); 
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        
        $dompdf->loadHtml($html);
        
        // **🚨 TAMAÑO DE PAPEL PARA TICKET (80mm)**
        $dompdf->setPaper([0, 0, 226.77, 600], 'portrait'); 
        
        $dompdf->render();

        ob_clean(); 

        // 3. Descargar/Mostrar el PDF
        $fileName = "PREFAC-INT-{$Boleta['id_EmitirBoletaInternado']}.pdf"; 
        $dompdf->stream($fileName, ["Attachment" => false]);
    }

    /**
     * Genera la estructura HTML de la Prefactura de Internado en formato Ticket (reducido) con desglose de IGV.
     */
    private function generarHtmlFactura($data)
    {
        // --- Preparación de Datos y CÁLCULOS IGV (18%) ---
        $tipo_comprobante = "PREFACTURA DE INTERNADO (EST.)";
        $igv_rate = 0.18;
        
        // Datos directamente de la tabla facturacion_internado
        $fecha_emision = date('d/m/Y H:i', strtotime($data['fecha_emision'] ?? 'now'));
        $id_factura = htmlspecialchars($data['id_factura'] ?? 'N/A');
        $id_internado = htmlspecialchars($data['id_internado'] ?? 'N/A');
        $dias_internado = htmlspecialchars($data['dias_internado'] ?? 0);
        
        // **Costos base para el cálculo**
        $monto_total_float = (float)($data['total'] ?? 0); 

        // Desglose del Total (asumiendo que $monto_total_float incluye el IGV)
        $total_bruto = $monto_total_float / (1 + $igv_rate);
        $igv = $monto_total_float - $total_bruto;

        // Formato para mostrar en el PDF
        $total_formato = number_format($monto_total_float, 2, '.', ',');
        $total_bruto_formato = number_format($total_bruto, 2, '.', ',');
        $igv_formato = number_format($igv, 2, '.', ',');
        
        $costo_habitacion = number_format((float)($data['costo_habitacion'] ?? 0), 2);
        $costo_tratamientos = number_format((float)($data['costo_tratamientos'] ?? 0), 2);
        $costo_medicamentos = number_format((float)($data['costo_medicamentos'] ?? 0), 2);
        $costo_otros = number_format((float)($data['costo_otros'] ?? 0), 2);
        
        // DATOS DEL PACIENTE (esperados de la consulta DAO)
        $paciente = htmlspecialchars($data['nombre_paciente'] ?? 'PACIENTE GENERAL');
        $documento_paciente = htmlspecialchars($data['id_paciente_doc'] ?? 'N/A'); 
        $fecha_ingreso = date('d/m/Y', strtotime($data['fecha_ingreso'] ?? 'N/A')); 
        $estado_doc = htmlspecialchars($data['estado'] ?? 'Pendiente');
        
        // --- Estructura HTML (Ticket Reducido) ---
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
            <style>
                /* Estilos Ticket */
                body { 
                    font-family: 'Courier New', Courier, monospace; 
                    font-size: 8pt; 
                    margin: 0; 
                    padding: 5px; 
                }
                .center { text-align: center; }
                .right { text-align: right; }
                .line { border-top: 1px dashed #000; margin: 5px 0; }
                .title { font-size: 10pt; font-weight: bold; }
                .small-text { font-size: 7pt; }
                .clearfix::after { content: ''; clear: both; display: table; }
                .detail-row { display: block; margin-bottom: 2px; }
                .detail-label { float: left; width: 60%; }
                .detail-value { float: right; width: 35%; text-align: right; }
                .item-desc { float: left; width: 70%; }
                .item-total { float: right; width: 25%; text-align: right; }
            </style>
        </head>
        <body>
            <div class='center title'>CLÍNICA VETERINARIA [GONZALEZ]</div>
            <div class='center small-text'>RUC: [00000000000]</div>
            <div class='center small-text'>[Dirección de la Clínica]</div>
            
            <div class='line'></div>
            
            <div class='center title'>{$tipo_comprobante}</div>
            <div class='center'>N°: {$id_factura}</div>
            
            <div class='line'></div>
            
            <div class='detail-row clearfix'>
                <span class='detail-label'>Fecha Emisión:</span>
                <span class='detail-value'>{$fecha_emision}</span>
            </div>
            <div class='detail-row clearfix'>
                <span class='detail-label'>Cliente:</span>
                <span class='detail-value'>{$paciente}</span>
            </div>
            <div class='detail-row clearfix'>
                <span class='detail-label'>ID/RUC:</span>
                <span class='detail-value'>{$documento_paciente}</span>
            </div>
            
            <div class='line'></div>

            <div class='detail-row small-text'>**INTERNADO ID:** {$id_internado}</div>
            <div class='detail-row small-text'>**INGRESO (EST.):** {$fecha_ingreso}</div>
            <div class='detail-row small-text'>**DÍAS ESTIMADOS:** {$dias_internado}</div>
            
            <div class='line'></div>
            
            <div class='clearfix title small-text' style='margin-bottom: 3px;'>
                <span class='item-desc'>**DESCRIPCIÓN DE COSTOS**</span>
                <span class='item-total'>**S/**</span>
            </div>
            
            <div class='clearfix'>
                <span class='item-desc small-text'>Habitación ({$dias_internado} días)</span>
                <span class='item-total'>{$costo_habitacion}</span>
            </div>
            <div class='clearfix'>
                <span class='item-desc small-text'>Tratamientos / Procedimientos</span>
                <span class='item-total'>{$costo_tratamientos}</span>
            </div>
            <div class='clearfix'>
                <span class='item-desc small-text'>Medicamentos</span>
                <span class='item-total'>{$costo_medicamentos}</span>
            </div>
            <div class='clearfix'>
                <span class='item-desc small-text'>Otros Costos / Adicionales</span>
                <span class='item-total'>{$costo_otros}</span>
            </div>
            
            <div class='line'></div>
            
            <div class='detail-row clearfix'>
                <span class='detail-label'>Subtotal (Venta Base):</span>
                <span class='detail-value'>S/ {$total_bruto_formato}</span>
            </div>
            <div class='detail-row clearfix'>
                <span class='detail-label'>I.G.V. (18%):</span>
                <span class='detail-value'>S/ {$igv_formato}</span>
            </div>
            
            <div class='line'></div>
            
            <div class='detail-row clearfix'>
                <span class='detail-label title'>TOTAL ESTIMADO:</span>
                <span class='detail-value title'>S/ {$total_formato}</span>
            </div>
            <div class='detail-row clearfix small-text'>
                <span class='detail-label'>Estado de la Pre-Factura:</span>
                <span class='detail-value'>{$estado_doc}</span>
            </div>

            <div class='line'></div>

            <div class='center small-text' style='margin-top: 10px;'>
                <p style='font-weight: bold; color: #ff0000;'>ESTE DOCUMENTO ES UNA PREFACTURA (ESTIMACIÓN).</p>
                <p>No es válido como Comprobante de Pago. Monto incluye IGV.</p>
                <p>GRACIAS POR SU PREFERENCIA</p>
            </div>
        </body>
        </html>";

        return $html;
    }
}
?>