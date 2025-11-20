<?php
require_once('../../../../../../dompdf/autoload.inc.php');
use Dompdf\Dompdf;

class formCitaMedicaPDF
{
    public function generarPDFShow($receta, $detalles)
    {
        $html = $this->generarHtmlRecetaCompleta($receta, $detalles);

        $dompdf = new Dompdf();
        
        // Configurar opciones
        $options = $dompdf->getOptions();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf->setOptions($options);
        
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nombreArchivo = "Receta-Completa-" . $receta['id_receta'] . ".pdf";
        $dompdf->stream($nombreArchivo, [
            "Attachment" => false,
            "compress" => true
        ]);
    }

    private function generarHtmlRecetaCompleta($receta, $detalles)
    {
        $fechaEmision = date('d/m/Y');
        $horaEmision = date('H:i');
        
        $html = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Receta Médica Completa - ' . $receta['id_receta'] . '</title>
            <style>
                @page { margin: 1.5cm; }
                body { 
                    font-family: "DejaVu Sans", "Arial", sans-serif; 
                    font-size: 12px; 
                    line-height: 1.4;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 20px; 
                    border-bottom: 3px double #2c3e50;
                    padding-bottom: 15px;
                }
                .clinic-name {
                    font-size: 22px;
                    font-weight: bold;
                    color: #2c3e50;
                    margin: 0;
                }
                .clinic-tagline {
                    font-size: 13px;
                    color: #7f8c8d;
                    margin: 5px 0;
                    font-style: italic;
                }
                .document-title {
                    font-size: 16px;
                    color: #e74c3c;
                    margin: 10px 0;
                    font-weight: bold;
                }
                .patient-info {
                    background: #ecf0f1;
                    padding: 15px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                    border-left: 5px solid #3498db;
                }
                .patient-name {
                    font-size: 16px;
                    font-weight: bold;
                    color: #2c3e50;
                    margin-bottom: 5px;
                }
                .doctor-info {
                    text-align: right;
                    margin-bottom: 20px;
                    padding: 10px;
                    border-bottom: 1px solid #bdc3c7;
                }
                .medications-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                .medications-table th {
                    background: #34495e;
                    color: white;
                    padding: 10px;
                    text-align: left;
                    font-weight: bold;
                }
                .medications-table td {
                    padding: 10px;
                    border: 1px solid #ddd;
                    vertical-align: top;
                }
                .medications-table tr:nth-child(even) {
                    background: #f8f9fa;
                }
                .instructions-section {
                    background: #fff3cd;
                    padding: 15px;
                    border: 1px solid #ffeaa7;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .signature-section {
                    margin-top: 60px;
                    text-align: center;
                }
                .signature-line {
                    border-top: 1px solid #333;
                    width: 300px;
                    margin: 40px auto 10px auto;
                    padding-top: 10px;
                }
                .footer {
                    margin-top: 50px;
                    text-align: center;
                    font-size: 10px;
                    color: #7f8c8d;
                    border-top: 1px solid #bdc3c7;
                    padding-top: 10px;
                }
                .watermark {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%) rotate(-45deg);
                    font-size: 80px;
                    color: rgba(231, 76, 60, 0.1);
                    z-index: -1;
                }
                .document-number {
                    background: #34495e;
                    color: white;
                    padding: 5px 15px;
                    border-radius: 20px;
                    display: inline-block;
                    margin: 10px 0;
                    font-size: 11px;
                }
                .important-note {
                    background: #e74c3c;
                    color: white;
                    padding: 10px;
                    border-radius: 5px;
                    margin: 15px 0;
                    text-align: center;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <!-- Marca de agua -->
            <div class="watermark">CLÍNICA GONZÁLEZ</div>

            <div class="header">
                <div class="clinic-name">Clínica González</div>
                <div class="clinic-tagline">90 años cuidando tu salud y la de los tuyos</div>
                <div class="document-title">RECETA MÉDICA COMPLETA</div>
                <div class="document-number">
                    N° ' . $receta['id_receta'] . ' | Emitido: ' . $fechaEmision . ' ' . $horaEmision . '
                </div>
            </div>

            <!-- Información del Médico -->
            <div class="doctor-info">
                <strong>Dr. ' . $receta['nombre_medico'] . '</strong><br>
                <em>Médico Tratante</em>
            </div>

            <!-- Información del Paciente -->
            <div class="patient-info">
                <div class="patient-name">' . $receta['nombre_paciente'] . '</div>
                <div>
                    <strong>DNI:</strong> ' . $receta['dni'] . ' | 
                    <strong>Fecha de Consulta:</strong> ' . date('d/m/Y', strtotime($receta['fecha'])) . '
                </div>
            </div>';

        // Indicaciones Generales
        if (!empty($receta['indicaciones_generales'])) {
            $html .= '
            <div class="instructions-section">
                <strong>INDICACIONES GENERALES:</strong><br>
                ' . nl2br($receta['indicaciones_generales']) . '
            </div>';
        }

        // Tabla de Medicamentos
        if (count($detalles) > 0) {
            $html .= '
            <table class="medications-table">
                <thead>
                    <tr>
                        <th width="25%">Medicamento</th>
                        <th width="15%">Dosis</th>
                        <th width="20%">Frecuencia</th>
                        <th width="15%">Duración</th>
                        <th width="25%">Notas/Instrucciones</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($detalles as $detalle) {
                $html .= '
                    <tr>
                        <td><strong>' . $detalle['medicamento'] . '</strong></td>
                        <td>' . $detalle['dosis'] . '</td>
                        <td>' . $detalle['frecuencia'] . '</td>
                        <td>' . ($detalle['duracion'] ?: 'No especificada') . '</td>
                        <td>' . ($detalle['notas'] ?: '-') . '</td>
                    </tr>';
            }
            
            $html .= '
                </tbody>
            </table>';
        } else {
            $html .= '
            <div style="text-align: center; padding: 20px; color: #7f8c8d;">
                <em>No se encontraron medicamentos registrados en esta receta.</em>
            </div>';
        }

        // Nota Importante
        $html .= '
            <div class="important-note">
                ⚠️ ESTA RECETA ES VÁLIDA POR 30 DÍAS A PARTIR DE LA FECHA DE EMISIÓN
            </div>

            <!-- Firma -->
            <div class="signature-section">
                <div class="signature-line"></div>
                <strong>Dr. ' . $receta['nombre_medico'] . '</strong><br>
                <em>Médico Tratante</em><br>';
                
        // Agregar CMP si está disponible
        if (isset($receta['cedula_profesional']) && !empty($receta['cedula_profesional'])) {
            $html .= 'CMP: ' . $receta['cedula_profesional'];
        }
                
        $html .= '
            </div>

            <!-- Footer -->
            <div class="footer">
                <p><strong>Clínica González</strong> - Más de 40 especialidades médicas</p>
                <p>📞 WhatsApp: 997584512 | 🌐 www.clinicagonzalez.com</p>
                <p>⏰ Horario de Atención: Lunes a Viernes 8:00 AM - 6:00 PM, Sábados 8:00 AM - 1:00 PM</p>
                <p style="font-size: 9px; margin-top: 10px;">
                    Documento generado el ' . $fechaEmision . ' a las ' . $horaEmision . ' | 
                    Sistema de Gestión Clínica | Página 1/1
                </p>
            </div>
        </body>
        </html>';

        return $html;
    }

    /**
     * Genera PDF simplificado para vista rápida
     */
    public function generarPDFSimple($receta, $detalles)
    {
        $html = $this->generarHtmlRecetaSimple($receta, $detalles);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nombreArchivo = "Receta-" . $receta['id_receta'] . ".pdf";
        $dompdf->stream($nombreArchivo, ["Attachment" => false]);
    }

    private function generarHtmlRecetaSimple($receta, $detalles)
    {
        $fechaEmision = date('d/m/Y');
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.3; }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; }
                .clinic-name { font-size: 18px; font-weight: bold; color: #2c3e50; margin: 0; }
                .document-title { font-size: 14px; color: #e74c3c; margin: 5px 0; }
                .patient-info { background: #ecf0f1; padding: 10px; margin: 10px 0; }
                .medications-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                .medications-table th, .medications-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .medications-table th { background: #34495e; color: white; }
                .signature { margin-top: 40px; text-align: center; }
                .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="clinic-name">Clínica González</div>
                <div class="document-title">RECETA MÉDICA #' . $receta['id_receta'] . '</div>
                <div>Emitido: ' . $fechaEmision . '</div>
            </div>

            <div class="patient-info">
                <strong>Paciente:</strong> ' . $receta['nombre_paciente'] . ' | 
                <strong>DNI:</strong> ' . $receta['dni'] . ' | 
                <strong>Médico:</strong> Dr. ' . $receta['nombre_medico'] . '
            </div>';

        if (count($detalles) > 0) {
            $html .= '
            <table class="medications-table">
                <tr><th>Medicamento</th><th>Dosis</th><th>Frecuencia</th><th>Duración</th></tr>';
            
            foreach ($detalles as $detalle) {
                $html .= '
                <tr>
                    <td>' . $detalle['medicamento'] . '</td>
                    <td>' . $detalle['dosis'] . '</td>
                    <td>' . $detalle['frecuencia'] . '</td>
                    <td>' . ($detalle['duracion'] ?: '-') . '</td>
                </tr>';
            }
            
            $html .= '</table>';
        }

        if (!empty($receta['indicaciones_generales'])) {
            $html .= '
            <div><strong>Indicaciones:</strong> ' . $receta['indicaciones_generales'] . '</div>';
        }

        $html .= '
            <div class="signature">
                _________________________<br>
                <strong>Dr. ' . $receta['nombre_medico'] . '</strong><br>
                Médico Tratante
            </div>

            <div class="footer">
                Clínica González - WhatsApp: 997584512 - www.clinicagonzalez.com
            </div>
        </body>
        </html>';

        return $html;
    }
}
?>