<?php

require_once('../../../../dompdf/autoload.inc.php'); // Asegúrate que la RUTA a Dompdf sea correcta
use Dompdf\Dompdf;
use Dompdf\Options;

class formEmicionBoletaPDF
{
    /**
     * Genera y muestra el PDF de la Boleta/Factura final en formato A4 (Diseño Moderno).
     * @param array $boleta Datos completos de la boleta, orden y paciente.
     */
    public function generarPDFShow($boleta)
    {
        // 1. Crear el HTML
        $html = $this->generarHtmlBoleta($boleta);

        // 2. Configurar y renderizar Dompdf
        $options = new Options();
        // Usar Arial (más formal/moderno)
        $options->set('defaultFont', 'Arial'); 
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        
        $dompdf->loadHtml($html);
        
        $dompdf->setPaper('A4', 'portrait'); 
        
        $dompdf->render();

        // Limpiar el búfer de salida
        ob_clean(); 

        // 3. Descargar/Mostrar el PDF
        // CORRECCIÓN PHP 5.x: Reemplazo '??' por isset()
        $tipo_doc = (isset($boleta['tipo']) ? $boleta['tipo'] : '') == 'Boleta' ? 'BOL' : 'FAC';
        $numero_boleta = isset($boleta['numero_boleta']) ? $boleta['numero_boleta'] : '000000';
        $fileName = "{$tipo_doc}-{$numero_boleta}.pdf";
        
        // Attachment => false muestra en el navegador; true lo descarga
        $dompdf->stream($fileName, ["Attachment" => false]);
    }

    /**
     * Genera la estructura HTML de la Boleta/Factura en formato HOJA COMPLETA (Estilo Moderno).
     * @param array $data Los datos de la boleta.
     */
    private function generarHtmlBoleta($data)
    {
        // --- Preparación de Datos (CORRECCIÓN PHP 5.x: Reemplazo '??' por isset()) ---
        $tipo_doc = isset($data['tipo']) ? $data['tipo'] : 'Boleta';
        $tipo_comprobante = strtoupper($tipo_doc == 'Boleta' ? 'BOLETA DE VENTA' : 'FACTURA');
        
        // --- Cálculo de IGV (Asumiendo 18%) ---
        $igv_rate = 0.18;
        $monto_total_float = (float)(isset($data['monto_total']) ? $data['monto_total'] : 0);
        $total_bruto = $monto_total_float / (1 + $igv_rate);
        $igv = $monto_total_float - $total_bruto;

        // Formato de moneda
        $monto_formato = number_format($monto_total_float, 2, '.', ',');
        $total_bruto_formato = number_format($total_bruto, 2, '.', ',');
        $igv_formato = number_format($igv, 2, '.', ',');
        
        // --- Datos de Cabecera/Cliente (CORRECCIÓN PHP 5.x: Reemplazo '??' por isset()) ---
        $fecha_boleta = date('d/m/Y H:i', strtotime(isset($data['fecha_emision']) ? $data['fecha_emision'] : 'now'));
        $nombre = isset($data['nombre_paciente']) ? $data['nombre_paciente'] : 'N/A';
        $apellido = isset($data['apellido_paterno']) ? $data['apellido_paterno'] : '';
        $paciente_completo = htmlspecialchars($nombre . ' ' . $apellido);
        
        $paciente_doc = htmlspecialchars(isset($data['id_paciente_doc']) ? $data['id_paciente_doc'] : 'N/A');
        $metodo = htmlspecialchars(isset($data['metodo_pago']) ? $data['metodo_pago'] : 'N/A');
        $concepto_desc = htmlspecialchars(isset($data['concepto']) ? $data['concepto'] : 'Servicio');
        $id_orden = htmlspecialchars(isset($data['id_orden']) ? $data['id_orden'] : 'N/A');
        $numero_boleta_html = htmlspecialchars(isset($data['numero_boleta']) ? $data['numero_boleta'] : '000000');


        // --- Estructura HTML (Estilo Moderno) ---
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
            <title>{$tipo_comprobante} - {$numero_boleta_html}</title>
            <style>
                /* Estilo Moderno y Llamativo */
                body { font-family: 'Arial', sans-serif; font-size: 10pt; margin: 25px; color: #333; }
                .clinic-title { color: #1A4F7F; font-size: 18pt; font-weight: bold; margin-bottom: 5px; }
                
                .document-box { 
                    border: 2px solid #1A4F7F; 
                    background-color: #F5F5F5; 
                    padding: 10px 15px; 
                    text-align: center;
                    border-radius: 8px;
                }
                .document-box .subtitle { color: #1A4F7F; font-size: 13pt; margin-bottom: 5px;}
                .document-box .doc-number { font-size: 20pt; font-weight: bold; color: #1A4F7F; }
                
                .client-details { 
                    width: 100%; 
                    border: 1px solid #ddd;
                    padding: 10px; 
                    margin-bottom: 20px; 
                    border-radius: 5px;
                    background-color: #F9F9F9;
                }
                .client-details td { padding: 5px; }
                .label-bold { font-weight: bold; color: #555; }

                /* Tabla de Detalle (Desglose) */
                .detail-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                .detail-table th, .detail-table td { border: 1px solid #ddd; padding: 10px 8px; text-align: left; }
                .detail-table th { 
                    background-color: #1A4F7F; 
                    color: white; 
                    text-align: center; 
                    font-size: 10.5pt;
                    border-color: #1A4F7F;
                }
                .detail-table .right { text-align: right; }
                
                /* Caja de Totales (Máximo contraste) */
                .totals-box { 
                    width: 45%; 
                    float: right; 
                    margin-top: 30px; 
                    
                }
                .totals-box table { width: 100%; }
                .totals-box td { padding: 8px; text-align: right; border: none; }
                .totals-box .total-label { 
                    font-weight: bold; 
                    font-size: 12pt; 
                    background-color: #38761D; /* Verde oscuro para el total */
                    color: white; 
                    text-align: left;
                    padding-left: 15px;
                    border-radius: 5px 0 0 5px;
                }
                 .totals-box .total-amount { 
                    font-weight: bold; 
                    font-size: 12pt; 
                    background-color: #38761D; 
                    color: white; 
                    border-radius: 0 5px 5px 0;
                }

                .center { text-align: center; }
                .right { text-align: right; }
                .footer { margin-top: 50px; padding-top: 10px; font-size: 9pt; border-top: 1px solid #ccc; }
                .subtitle-section { font-size: 12pt; font-weight: bold; color: #1A4F7F; margin-top: 15px; margin-bottom: 10px;}
            </style>
        </head>
        <body>
            <table style='width: 100%; margin-bottom: 20px;'>
                <tr>
                    <td style='width: 55%;'>
                        <div class='clinic-title'>CLÍNICA VETERINARIA [GONZALEZ]</div>
                        <div>RUC: [00000000000]</div>
                        <div>[Dirección de la Clínica]</div>
                    </td>
                    <td style='width: 45%;'>
                        <div class='document-box'>
                            <div class='subtitle'>{$tipo_comprobante}</div>
                            <div class='doc-number'>N°: {$numero_boleta_html}</div>
                        </div>
                    </td>
                </tr>
            </table>

            <div class='subtitle-section'>Detalles del Cliente y Pago</div>
            <table class='client-details'>
                <tr>
                    <td style='width: 60%;'><span class='label-bold'>CLIENTE:</span> {$paciente_completo}</td>
                    <td style='width: 40%;'><span class='label-bold'>FECHA EMISIÓN:</span> {$fecha_boleta}</td>
                </tr>
                <tr>
                    <td><span class='label-bold'>ID/RUC:</span> {$paciente_doc}</td>
                    <td><span class='label-bold'>MÉTODO PAGO:</span> {$metodo}</td>
                </tr>
            </table>

            <div class='subtitle-section'>Detalle de la Transacción</div>
            <table class='detail-table'>
                <thead>
                    <tr>
                        <th style='width: 75%;'>DESCRIPCIÓN</th>
                        <th style='width: 25%;'>IMPORTE (S/)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Servicio principal: ".htmlspecialchars($concepto_desc)." (Orden N° {$id_orden})</td>
                        <td class='right'>{$monto_formato}</td>
                    </tr>
                    <tr><td colspan='2' style='height: 30px; border-bottom: none;'></td></tr>
                </tbody>
            </table>
            
            <div class='totals-box'>
                <table style='border: 1px solid #ddd; border-radius: 5px;'>
                    <tr>
                        <td style='width: 60%;'>SUBTOTAL:</td>
                        <td class='right' style='width: 40%;'>S/ {$total_bruto_formato}</td>
                    </tr>
                    <tr>
                        <td>I.G.V. (18%):</td>
                        <td class='right'>S/ {$igv_formato}</td>
                    </tr>
                    <tr>
                        <td class='total-label'>TOTAL PAGADO:</td>
                        <td class='total-amount'>S/ {$monto_formato}</td>
                    </tr>
                </table>
            </div>

            <div style='clear: both;'></div>

            <div class='center footer'>
                <p style='font-weight: bold;'>GRACIAS POR SU PREFERENCIA</p>
                <p>--- Comprobante Válido para SUNAT ---</p>
            </div>

        </body>
        </html>";

        return $html;
    }
}