<?php
// formFacturacionInternadoPDF.php

require_once('../../../../dompdf/autoload.inc.php'); 
use Dompdf\Dompdf;
use Dompdf\Options;

class formFacturacionInternadoPDF
{
    
    public function generarPDFShow($factura)
    {
        // 1. Crear el HTML
        $html = $this->generarHtmlFactura($factura);

        // 2. Configurar y renderizar Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait'); 
        $dompdf->render();

        ob_clean(); 

        // 3. Descargar/Mostrar el PDF
        $fileName = "PREFAC-INT-{$factura['id_factura']}.pdf"; // CAMBIO DE NOMBRE A PREFAC
        $dompdf->stream($fileName, ["Attachment" => false]);
    }

    /**
     * Genera la estructura HTML de la Prefactura de Internado (Hoja Completa) 
     * utilizando solo datos de facturacion_internado y campos de relleno.
     */
    private function generarHtmlFactura($data)
    {
        // --- Preparación de Datos ---
        $tipo_comprobante = "PREFACTURA DE INTERNADO";
        
        // Datos directamente de la tabla facturacion_internado
        $fecha_emision = date('d/m/Y', strtotime($data['fecha_emision'] ?? 'now'));
        $id_internado = htmlspecialchars($data['id_internado'] ?? 'N/A');
        $dias_internado = htmlspecialchars($data['dias_internado'] ?? 0);
        
        // Costos
        $total = number_format((float)($data['total'] ?? 0), 2);
        $costo_habitacion = number_format((float)($data['costo_habitacion'] ?? 0), 2);
        $costo_tratamientos = number_format((float)($data['costo_tratamientos'] ?? 0), 2);
        $costo_medicamentos = number_format((float)($data['costo_medicamentos'] ?? 0), 2);
        $costo_otros = number_format((float)($data['costo_otros'] ?? 0), 2);
        
        // Datos de relleno estáticos (ya que no se usa JOIN)
        $paciente = 'PACIENTE DESCONOCIDO (Estimación)';
        $documento_paciente = 'N/A';
        $fecha_ingreso = 'N/A'; 
        
        // Asumimos que el total ya es el MONTO ESTIMADO COMPLETO, sin desagregar IGV en prefactura.
        
        // --- Estructura HTML (Hoja Completa) ---
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
            <style>
                body { font-family: 'Arial', sans-serif; font-size: 10pt; margin: 20px; }
                .header-info { width: 100%; margin-bottom: 20px; }
                .document-box { border: 1px solid #000; padding: 10px; text-align: right; }
                .client-details, .internado-details { width: 100%; border: 1px solid #000; padding: 10px; margin-bottom: 20px; border-radius: 5px;}
                .client-details td, .internado-details td { padding: 3px; }
                
                .detail-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                .detail-table th, .detail-table td { border: 1px solid #000; padding: 8px; text-align: left; }
                .detail-table th { background-color: #f0f0f0; text-align: center; }
                
                .totals-box { width: 40%; float: right; margin-top: 20px; border: 1px solid #000;}
                .totals-box td { padding: 5px; text-align: right; }
                
                .center { text-align: center; }
                .right { text-align: right; }
                .title { font-size: 14pt; font-weight: bold; }
                .subtitle { font-size: 12pt; font-weight: bold; }
            </style>
        </head>
        <body>
            <table class='header-info'>
                <tr>
                    <td style='width: 55%;'>
                        <div class='title'>CLÍNICA VETERINARIA [GONZALEZ]</div>
                        <div>RUC: [00000000000]</div>
                        <div>[Dirección de la Clínica]</div>
                    </td>
                    <td style='width: 45%;' class='document-box'>
                        <div class='subtitle'>{$tipo_comprobante}</div>
                        <div style='font-size: 16pt; font-weight: bold;'>N°: {$data['id_factura']}</div>
                        <div style='margin-top: 5px; font-size: 10pt;'>Fecha de Emisión: {$fecha_emision}</div>
                    </td>
                </tr>
            </table>

            <table class='client-details'>
                <tr>
                    <td style='width: 60%;'>**CLIENTE:** {$paciente}</td>
                    <td style='width: 40%;'>**RUC/ID:** {$documento_paciente}</td>
                </tr>
                <tr>
                    <td>**DIRECCIÓN:** [No disponible en Prefactura]</td>
                    <td>**ESTADO:** {$data['estado']} (Estimación)</td>
                </tr>
            </table>

            <div class='subtitle' style='margin-bottom: 5px;'>Detalle del Internamiento</div>
            <table class='internado-details'>
                <tr>
                    <td style='width: 33%;'>**ID INTERNADO:** {$id_internado}</td>
                    <td style='width: 33%;'>**FECHA INGRESO (EST.):** {$fecha_ingreso}</td>
                    <td style='width: 34%;'>**DÍAS ESTIMADOS:** {$dias_internado}</td>
                </tr>
            </table>
            
            <div class='subtitle' style='margin-bottom: 5px;'>Desglose de Costos Estimados</div>
            <table class='detail-table'>
                <thead>
                    <tr>
                        <th style='width: 70%;'>CONCEPTO</th>
                        <th style='width: 30%;'>MONTO ESTIMADO (S/)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Costo por Días de Habitación ({$dias_internado} días)</td><td class='right'>{$costo_habitacion}</td></tr>
                    <tr><td>Costo Estimado de Tratamientos y Procedimientos</td><td class='right'>{$costo_tratamientos}</td></tr>
                    <tr><td>Costo Estimado de Medicamentos Suministrados</td><td class='right'>{$costo_medicamentos}</td></tr>
                    <tr><td>Otros Costos Estimados / Servicios Adicionales</td><td class='right'>{$costo_otros}</td></tr>
                </tbody>
            </table>
            
            <table class='totals-box'>
                <tr>
                    <td style='font-weight: bold; font-size: 11pt;'>MONTO TOTAL ESTIMADO:</td>
                    <td style='font-weight: bold; font-size: 11pt;'>S/ {$total}</td>
                </tr>
                <tr>
                    <td>Método de Pago:</td>
                    <td>PENDIENTE</td>
                </tr>
            </table>

            <div style='clear: both;'></div>

            <div style='margin-top: 50px; border-top: 1px solid #000; padding-top: 10px; font-size: 8pt;' class='center'>
                <p style='font-weight: bold; color: #ff0000;'>ADVERTENCIA: ESTE DOCUMENTO ES UNA PREFACTURA (ESTIMACIÓN).</p>
                <p>No es válido como Comprobante de Pago. El monto final puede variar.</p>
                <p>GRACIAS POR SU PREFERENCIA</p>
            </div>

        </body>
        </html>";

        return $html;
    }
}
?>