<?php
// FILE: formOdenPDF.php

require_once('../../../../dompdf/autoload.inc.php');
use Dompdf\Dompdf;
use Dompdf\Options;

// PATRÓN: FACTORY METHOD (Creación de Opciones)
class PdfOptionsFactory {
    // ATRIBUTO estático (constante)
    const DEFAULT_FONT = 'Courier';
    
    // MÉTODO (Creación de un objeto de opciones con configuración específica)
    public static function createTicketOptions(): Options {
        $options = new Options();
        $options->set('defaultFont', self::DEFAULT_FONT); // Uso de ATRIBUTO
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        return $options;
    }
}

/**
 * PATRÓN: TEMPLATE METHOD (Clase Abstracta o Base, aunque la dejamos concreta por simplicidad)
 * Define el esqueleto fijo para generar el PDF.
 */
class formOdenPDF
{
    /**
     * MÉTODO (El Template Method principal que define el algoritmo fijo)
     * @param array $orden Datos de la orden a procesar.
     */
    public function generarPDFShow($orden)
    {
        // 1. PASO: Generar el HTML (Implementación concreta en un método)
        $html = $this->generarHtmlOrden($orden);

        // 2. PASO: Configurar el renderizador (Uso de Factory Method)
        $options = PdfOptionsFactory::createTicketOptions(); // Uso del PATRÓN Factory Method
        $dompdf = new Dompdf($options);
        
        // 3. PASO: Cargar contenido
        $dompdf->loadHtml($html);

        // 4. PASO: Configurar papel (Hook o paso específico)
        $dompdf->setPaper([0, 0, 226.77, 600], 'portrait');

        // 5. PASO: Renderizar y mostrar
        $dompdf->render();

        // 6. PASO: Stream (Paso final)
        $nombreArchivo = "ORDEN-PREFAC-" . $orden['id_orden'] . ".pdf";
        $dompdf->stream($nombreArchivo, ["Attachment" => false]);
    }

    /**
     * MÉTODO (Paso de implementación concreta del Template Method: Generar HTML)
     */
    private function generarHtmlOrden($orden)
    {
        // --- Preparación de Datos y CÁLCULOS IGV (18%) ---
        $igv_rate = 0.18; // ATRIBUTO (constante local)
        $monto_estimado = (float)($orden['monto_estimado'] ?? 0);
        
        // CÁLCULOS
        $total_bruto = $monto_estimado / (1 + $igv_rate);
        $igv = $monto_estimado - $total_bruto;

        // Formato para mostrar en el PDF
        $total_formato = number_format($monto_estimado, 2, '.', ',');
        $total_bruto_formato = number_format($total_bruto, 2, '.', ',');
        $igv_formato = number_format($igv, 2, '.', ',');

        // Datos formateados y seguros
        $fecha_emision = date('d/m/Y H:i', strtotime($orden['fecha_emision'] ?? 'now'));
        $id_orden = htmlspecialchars($orden['id_orden'] ?? 'N/A');
        // ... (otros datos de la orden)

        // --- Estructura HTML (Ticket Reducido) ---
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style> /* ... CSS styles ... */ </style>
        </head>
        <body>
        <div class="center title">ORDEN DE PREFACTURA</div>
        <div class="center">N°: ' . $id_orden . '</div>
        
        <div class="line"></div>
        
        <div class="detail-row clearfix">
            <span class="detail-label title">TOTAL ESTIMADO:</span>
            <span class="detail-value title">S/ ' . $total_formato . '</span>
        </div>

        <div class="line"></div>

        <div class="center small-text" style="margin-top: 10px;">
            <p class="warning">ORDEN DE PREFACTURA - NO VÁLIDA COMO COMPROBANTE</p>
            <p>TOTAL estimado: S/ ' . $total_formato . '</p>
            <p>Presente esta orden en Caja para proceder con la facturación final.</p>
        </div>
        </body>
        </html>';

        return $html;
    }
}
?>