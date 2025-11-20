<?php
require_once('../../../../../dompdf/autoload.inc.php');
use Dompdf\Dompdf;

class formOrdenExamenClinicoPDF
{
    /**
     * Genera y muestra el PDF de la orden de examen
     */
    public function generarPDFShow($orden)
    {
        $html = $this->generarHtmlOrden($orden);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nombreArchivo = "Orden_Examen-N-" . $orden['id_orden'] . ".pdf";
        $dompdf->stream($nombreArchivo, ["Attachment" => false]);
        exit();
    }

    /**
     * Genera el HTML para el documento PDF
     */
    private function generarHtmlOrden($orden)
    {
        // Determina el color del estado
        $estadoColor = match ($orden['estado']) {
            'Pendiente' => 'red',
            'Realizado' => 'orange',
            'Entregado' => 'green',
            default => '#555',
        };

        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; font-size: 11px; }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
                .header h2 { color: #007bff; margin-bottom: 2px; }
                .header p { margin: 0; font-size: 10px; }
                .section-title { background-color: #f2f2f2; padding: 5px; margin-top: 15px; margin-bottom: 5px; font-weight: bold; border-left: 5px solid #007bff;}
                .info-box p { margin: 3px 0; }
                .content { white-space: pre-wrap; margin: 10px 0; border: 1px solid #ccc; padding: 10px; min-height: 100px;}
                .signature { margin-top: 50px; text-align: center; }
                .signature div { border-top: 1px solid #000; width: 50%; margin: 0 auto; padding-top: 5px; }
                .clinic-info { text-align: right; font-size: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>CLÍNICA GONZÁLEZ</h2>
                <p>90 años cuidando tu salud y la de los tuyos | WSP 997584512</p>
                <h3>ORDEN DE EXAMEN CLÍNICO N° ' . htmlspecialchars($orden['id_orden']) . '</h3>
            </div>
            
            <div class="clinic-info">
                Fecha Emisión: ' . date('d/m/Y', strtotime($orden['fecha'])) . '
            </div>

            <div class="info-box">
                <p><strong>Paciente:</strong> ' . htmlspecialchars($orden['nombre_paciente']) . ' (DNI: ' . htmlspecialchars($orden['dni']) . ')</p>
                <p><strong>Historia Clínica ID:</strong> ' . htmlspecialchars($orden['historia_clinica_id']) . '</p>
                <p><strong>Dr. Tratante:</strong> ' . htmlspecialchars($orden['nombre_medico']) . '</p>
            </div>
            
            <div class="section-title">🔬 TIPO DE EXAMEN SOLICITADO</div>
            <p style="font-size: 14px; font-weight: bold; color: #333;">' . htmlspecialchars($orden['tipo_examen']) . '</p>

            <div class="section-title">📝 EXÁMENES ESPECÍFICOS / INDICACIONES DETALLADAS</div>
            <div class="content">
                ' . nl2br(htmlspecialchars($orden['indicaciones'])) . '
            </div>
            
            <div class="section-title">🛑 ESTADO ACTUAL DE LA ORDEN</div>
            <p>La orden se encuentra en estado: <span style="font-weight: bold; color: ' . $estadoColor . ';">' . htmlspecialchars($orden['estado']) . '</span>.</p>

            <div class="signature">
                <div>Firma del Médico Tratante</div>
                <small>' . htmlspecialchars($orden['nombre_medico']) . '</small>
            </div>
            
            <div style="margin-top: 30px; text-align: center; font-size: 9px; color: #555;">
                <p>Presente esta orden en el Laboratorio o área correspondiente.</p>
            </div>
        </body>
        </html>';

        return $html;
    }
}