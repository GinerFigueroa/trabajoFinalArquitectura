<?php

require_once('../../../../dompdf/autoload.inc.php'); 
use Dompdf\Dompdf;

class formConcentimientoInformadoPDF
{
    public function generarPDFShow($consentimiento)
    {
        // 1. Crear el HTML específico para el Consentimiento Informado
        $html = $this->generarHtmlConsentimiento($consentimiento);

        // 2. Configurar y renderizar Dompdf
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 3. Mostrar el PDF en el navegador
        $nombreArchivo = "Consentimiento-ID-" . $consentimiento['consentimiento_id'] . ".pdf";
        $dompdf->stream($nombreArchivo, ["Attachment" => false]);
    }

    private function generarHtmlConsentimiento($consentimiento)
    {
        // Se asume que $consentimiento contiene:
        // 'consentimiento_id', 'nombre_paciente_completo', 'historia_clinica_id',
        // 'nombre_medico_completo', 'fecha_firma', 'diagnostico_descripcion',
        // 'tratamiento_descripcion'
        
        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; font-size: 11px; margin: 40px; }
                h2 { text-align: center; color: #004d99; margin-bottom: 20px; }
                .data-box { border: 1px solid #ccc; padding: 10px; margin-bottom: 15px; }
                .content-box { border: 1px dashed #aaa; padding: 15px; margin-top: 20px; }
                .signature { margin-top: 80px; text-align: center; }
            </style>
        </head>
        <body>
            <h2>CONSENTIMIENTO INFORMADO</h2>
            <div class="data-box">
                <strong>ID Consentimiento:</strong> ' . htmlspecialchars($consentimiento['consentimiento_id']) . ' | 
                <strong>Fecha de Firma:</strong> ' . date('d/m/Y H:i', strtotime($consentimiento['fecha_firma'])) . '<br>
                <strong>Paciente:</strong> ' . htmlspecialchars($consentimiento['nombre_paciente_completo']) . ' (HC N° ' . htmlspecialchars($consentimiento['historia_clinica_id']) . ')<br>
                <strong>Dr. Tratante:</strong> ' . htmlspecialchars($consentimiento['nombre_medico_completo']) . '
            </div>

            <h4>DIAGNÓSTICO / MOTIVO DE INTERVENCIÓN</h4>
            <div class="content-box">
                ' . nl2br(htmlspecialchars($consentimiento['diagnostico_descripcion'])) . '
            </div>

            <h4>PROCEDIMIENTO / TRATAMIENTO INFORMADO</h4>
            <div class="content-box">
                ' . nl2br(htmlspecialchars($consentimiento['tratamiento_descripcion'])) . '
            </div>
            
            <p style="margin-top: 30px;">Yo, ' . htmlspecialchars($consentimiento['nombre_paciente_completo']) . ', declaro haber leído y entendido la información proporcionada por el Dr. Tratante con respecto a mi diagnóstico y al procedimiento/tratamiento propuesto. He tenido la oportunidad de hacer preguntas y se me ha respondido satisfactoriamente. Conozco los riesgos y beneficios, y doy mi **consentimiento voluntario**.</p>

            <div class="signature">
                <hr style="width: 50%; margin: auto;">
                <p>Firma del Paciente</p>
            </div>
        </body>
        </html>';

        return $html;
    }
}
