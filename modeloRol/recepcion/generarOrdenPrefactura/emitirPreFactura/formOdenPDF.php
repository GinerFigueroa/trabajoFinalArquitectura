<?php

require_once('../../../../dompdf/autoload.inc.php');
use Dompdf\Dompdf;
use Dompdf\Options;

class formOdenPDF
{
    public function generarPDFShow($orden)
    {
        // 1. Crear el HTML
        $html = $this->generarHtmlOrden($orden);

        // 2. Configurar y renderizar Dompdf con estilo TICKET
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        
        // Cargar HTML en Dompdf
        $dompdf->loadHtml($html);

        // Configurar tamaño de papel TICKET (80mm)
        $dompdf->setPaper([0, 0, 226.77, 600], 'portrait');

        // Renderizar PDF
        $dompdf->render();

        // Mostrar el PDF en el navegador
        $nombreArchivo = "ORDEN-PREFAC-" . $orden['id_orden'] . ".pdf";
        $dompdf->stream($nombreArchivo, ["Attachment" => false]);
    }

    private function generarHtmlOrden($orden)
    {
        // --- Preparación de Datos y CÁLCULOS IGV (18%) ---
        $igv_rate = 0.18;
        $monto_estimado = (float)($orden['monto_estimado'] ?? 0);
        
        // Desglose del Total (asumiendo que incluye IGV)
        $total_bruto = $monto_estimado / (1 + $igv_rate);
        $igv = $monto_estimado - $total_bruto;

        // Formato para mostrar en el PDF
        $total_formato = number_format($monto_estimado, 2, '.', ',');
        $total_bruto_formato = number_format($total_bruto, 2, '.', ',');
        $igv_formato = number_format($igv, 2, '.', ',');

        // Datos formateados y seguros
        $fecha_emision = date('d/m/Y H:i', strtotime($orden['fecha_emision'] ?? 'now'));
        $id_orden = htmlspecialchars($orden['id_orden'] ?? 'N/A');
        $nombre_paciente = htmlspecialchars($orden['nombre_paciente_completo'] ?? 'PACIENTE GENERAL');
        $dni_paciente = htmlspecialchars($orden['dni_paciente'] ?? 'N/A');
        $estado = htmlspecialchars($orden['estado'] ?? 'Pendiente');
        $concepto = nl2br(htmlspecialchars($orden['concepto'] ?? 'Servicios médicos'));

        // --- Estructura HTML (Ticket Reducido) ---
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <style>
                /* Estilos Ticket - Mismo estilo que facturación internado */
                body { 
                    font-family: "Courier New", Courier, monospace; 
                    font-size: 8pt; 
                    margin: 0; 
                    padding: 5px; 
                    line-height: 1.2;
                }
                .center { text-align: center; }
                .right { text-align: right; }
                .left { text-align: left; }
                .line { border-top: 1px dashed #000; margin: 5px 0; }
                .title { font-size: 10pt; font-weight: bold; }
                .small-text { font-size: 7pt; }
                .clearfix::after { content: ""; clear: both; display: table; }
                .detail-row { display: block; margin-bottom: 2px; }
                .detail-label { float: left; width: 60%; }
                .detail-value { float: right; width: 35%; text-align: right; }
                .item-desc { float: left; width: 70%; }
                .item-total { float: right; width: 25%; text-align: right; }
                .concepto-box { 
                    border: 1px dashed #666; 
                    padding: 3px; 
                    margin: 5px 0; 
                    font-size: 7pt;
                    min-height: 40px;
                }
                .warning { font-weight: bold; color: #ff0000; }
                .contact-info { font-size: 6pt; margin-top: 2px; }
            </style>
        </head>
        <body>
            <div class="center title">CLÍNICA GONZALEZ</div>
            <div class="center small-text">RUC: 20123456789</div>
            <div class="center small-text">Av. Javier Prado Este 123, San Isidro</div>
            <div class="center contact-info">WhatsApp: 997-584-512 | www.clinicagonzalez.com</div>
            
            <div class="line"></div>
            
            <div class="center title">ORDEN DE PREFACTURA</div>
            <div class="center">N°: ' . $id_orden . '</div>
            
            <div class="line"></div>
            
            <div class="detail-row clearfix">
                <span class="detail-label">Fecha Emisión:</span>
                <span class="detail-value">' . $fecha_emision . '</span>
            </div>
            <div class="detail-row clearfix">
                <span class="detail-label">Paciente:</span>
                <span class="detail-value">' . $nombre_paciente . '</span>
            </div>
            <div class="detail-row clearfix">
                <span class="detail-label">DNI:</span>
                <span class="detail-value">' . $dni_paciente . '</span>
            </div>
            <div class="detail-row clearfix">
                <span class="detail-label">Estado:</span>
                <span class="detail-value">' . $estado . '</span>
            </div>
            
            <div class="line"></div>

            <div class="center small-text title">DESCRIPCIÓN DEL SERVICIO</div>
            <div class="concepto-box">' . $concepto . '</div>
            
            <div class="line"></div>
            
            <div class="clearfix title small-text" style="margin-bottom: 3px;">
                <span class="item-desc">**DETALLE DE COSTOS ESTIMADOS**</span>
                <span class="item-total">**S/**</span>
            </div>
            
            <div class="clearfix">
                <span class="item-desc small-text">Servicios Médicos/Procedimientos</span>
                <span class="item-total">' . $total_formato . '</span>
            </div>
            
            <div class="line"></div>
            
            <div class="detail-row clearfix">
                <span class="detail-label">Subtotal (Venta Base):</span>
                <span class="detail-value">S/ ' . $total_bruto_formato . '</span>
            </div>
            <div class="detail-row clearfix">
                <span class="detail-label">I.G.V. (18%):</span>
                <span class="detail-value">S/ ' . $igv_formato . '</span>
            </div>
            
            <div class="line"></div>
            
            <div class="detail-row clearfix">
                <span class="detail-label title">TOTAL ESTIMADO:</span>
                <span class="detail-value title">S/ ' . $total_formato . '</span>
            </div>

            <div class="line"></div>

            <div class="center small-text" style="margin-top: 10px;">
                <p class="warning">ORDEN DE PREFACTURA - NO VÁLIDA COMO COMPROBANTE</p>
                <p>Presente esta orden en Caja para proceder con la facturación final.</p>
                <p>Monto incluye IGV. Generado por Recepcionista.</p>
                <p>GRACIAS POR SU PREFERENCIA</p>
            </div>
        </body>
        </html>';

        return $html;
    }
}