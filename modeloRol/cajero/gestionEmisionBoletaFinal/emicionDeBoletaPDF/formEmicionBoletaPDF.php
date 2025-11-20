<?php

require_once('../../../../dompdf/autoload.inc.php'); // Asegúrate que la RUTA a Dompdf sea correcta
use Dompdf\Dompdf;
use Dompdf\Options;

class formEmicionBoletaPDF
{
    /**
     * Genera y muestra el PDF de la Boleta/Factura final en formato Ticket.
     * @param array $boleta Datos completos de la boleta, orden y paciente.
     */
    public function generarPDFShow($boleta)
    {
        // 1. Crear el HTML
        $html = $this->generarHtmlBoleta($boleta);

        // 2. Configurar y renderizar Dompdf
        $options = new Options();
        // Usar una fuente que simule ticket
        $options->set('defaultFont', 'Courier'); 
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        
        $dompdf->loadHtml($html);
        
        // *** AJUSTE PARA FORMATO TICKET (80mm de ancho) ***
        // El tamaño de papel [0, 0, 226.77, 566.93] es aproximadamente 80mm x 200mm.
        // El tamaño de la altura puede ajustarse según la longitud del contenido.
        $dompdf->setPaper([0, 0, 226.77, 600], 'portrait'); 
        
        $dompdf->render();

        // **CORRECCIÓN CLAVE:** Limpiar el búfer de salida antes de enviar las cabeceras del PDF
        ob_clean(); 

        // 3. Descargar/Mostrar el PDF
        $tipo_doc = ($boleta['tipo'] == 'Boleta') ? 'BOL' : 'FAC';
        $fileName = "{$tipo_doc}-{$boleta['numero_boleta']}.pdf";
        
        // Attachment => false muestra en el navegador; true lo descarga
        $dompdf->stream($fileName, ["Attachment" => false]);
    }

    /**
     * Genera la estructura HTML de la Boleta/Factura en formato reducido (Ticket).
     * @param array $data Los datos de la boleta.
     */
    private function generarHtmlBoleta($data)
    {
        // --- Preparación de Datos ---
        $tipo_doc = $data['tipo'];
        $tipo_comprobante = strtoupper($tipo_doc == 'Boleta' ? 'BOLETA DE VENTA' : 'FACTURA');
        
        // --- Cálculo de IGV (Asumiendo 18%) ---
        $igv_rate = 0.18;
        $monto_total_float = (float)($data['monto_total'] ?? 0);
        $total_bruto = $monto_total_float / (1 + $igv_rate);
        $igv = $monto_total_float - $total_bruto;

        $monto_formato = number_format($monto_total_float, 2, '.', ',');
        $total_bruto_formato = number_format($total_bruto, 2, '.', ',');
        $igv_formato = number_format($igv, 2, '.', ',');
        
        // --- Datos de Cabecera/Cliente ---
        $fecha_boleta = date('d/m/Y H:i', strtotime($data['fecha_emision']));
        $paciente = htmlspecialchars($data['nombre_paciente'] . ' ' . $data['apellido_paterno']);
        $metodo = htmlspecialchars($data['metodo_pago']);
        
        // --- HTML de la Boleta Reducida (Ticket) ---
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
            <title>{$tipo_comprobante} - {$data['numero_boleta']}</title>
            <style>
                body { font-family: 'Courier New', Courier, monospace; font-size: 8pt; margin: 0; padding: 5px; }
                .center { text-align: center; }
                .left { text-align: left; }
                .right { text-align: right; }
                .line { border-top: 1px dashed #000; margin: 5px 0; }
                .title { font-size: 10pt; font-weight: bold; }
                .small-text { font-size: 7pt; }
                .clearfix::after { content: ''; clear: both; display: table; }
                .detail-row { display: block; margin-bottom: 2px; }
                .detail-label { float: left; width: 60%; }
                .detail-value { float: right; width: 35%; text-align: right; }
                .footer { margin-top: 10px; padding-top: 5px; }
            </style>
        </head>
        <body>
            <div class='center title'>CLÍNICA VETERINARIA [GONZALEZ]</div>
            <div class='center small-text'>RUC: [00000000000]</div>
            <div class='center small-text'>[Dirección de la Clínica]</div>
            
            <div class='line'></div>
            
            <div class='center title'>{$tipo_comprobante}</div>
            <div class='center'>N°: {$data['numero_boleta']}</div>
            
            <div class='line'></div>
            
            <div class='detail-row clearfix'>
                <span class='detail-label'>Fecha Emisión:</span>
                <span class='detail-value'>{$fecha_boleta}</span>
            </div>
            <div class='detail-row clearfix'>
                <span class='detail-label'>Cliente:</span>
                <span class='detail-value'>{$paciente}</span>
            </div>";

        // Mostrar ID/RUC si es Factura o si la Boleta es por un monto alto (ej. > 700 S/)
        if ($tipo_doc == 'Factura' || $monto_total_float > 700) {
            $html .= "
            <div class='detail-row clearfix'>
                <span class='detail-label'>ID/RUC:</span>
                <span class='detail-value'>".htmlspecialchars($data['id_paciente_doc'] ?? 'N/A')."</span>
            </div>";
        }

        $html .= "
            <div class='detail-row clearfix'>
                <span class='detail-label'>Método de Pago:</span>
                <span class='detail-value'>{$metodo}</span>
            </div>
            
            <div class='line'></div>
            
            <div class='clearfix'>
                <span style='float: left; width: 70%;'>**DESCRIPCIÓN**</span>
                <span style='float: right; width: 25%; text-align: right;'>**TOTAL**</span>
            </div>
            <div class='line'></div>

            <div class='clearfix'>
                <span style='float: left; width: 70%;' class='small-text'>
                    Servicio principal: ".htmlspecialchars($data['concepto'])." (Orden N° {$data['id_orden']})
                </span>
                <span style='float: right; width: 25%; text-align: right;'>S/ {$monto_formato}</span>
            </div>
            
            <div class='line'></div>
            
            <div class='detail-row clearfix'>
                <span class='detail-label'>Subtotal:</span>
                <span class='detail-value'>S/ {$total_bruto_formato}</span>
            </div>
            <div class='detail-row clearfix'>
                <span class='detail-label'>I.G.V. (18%):</span>
                <span class='detail-value'>S/ {$igv_formato}</span>
            </div>
            <div class='detail-row clearfix title' style='margin-top: 5px;'>
                <span class='detail-label'>TOTAL PAGADO:</span>
                <span class='detail-value'>S/ {$monto_formato}</span>
            </div>
            
            <div class='line'></div>
            
            <div class='center small-text footer'>
                <p>GRACIAS POR SU PREFERENCIA</p>
                <p>--- Comprobante Válido para SUNAT ---</p>
            </div>
        </body>
        </html>";

        return $html;
    }
}