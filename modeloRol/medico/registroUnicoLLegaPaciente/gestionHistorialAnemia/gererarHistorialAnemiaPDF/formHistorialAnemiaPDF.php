<?php
require_once('../../../../../dompdf/autoload.inc.php');
use Dompdf\Dompdf;

class formHistorialAnemiaPDF
{
    public function generarPDFShow($historial)
    {
        // 1. Crear el HTML
        $html = $this->generarHtmlHistorial($historial);

        // 2. Configurar y renderizar Dompdf
        $dompdf = new Dompdf();
        
        // Cargar HTML en Dompdf
        $dompdf->loadHtml($html, 'UTF-8');

        // Configurar tamaño de papel
        $dompdf->setPaper('A4', 'portrait');

        // Renderizar PDF
        $dompdf->render();

        // Mostrar el PDF en el navegador
        $nombreArchivo = "Historial-Anemia-" . $historial['nombre_paciente'] . "-" . date('Y-m-d') . ".pdf";
        $dompdf->stream($nombreArchivo, ["Attachment" => false]);
    }

    private function generarHtmlHistorial($historial)
    {
        $html = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Historial de Anemia - ' . htmlspecialchars($historial['nombre_paciente']) . '</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    font-size: 12px; 
                    line-height: 1.4;
                    background: linear-gradient(to bottom, white 0%, white 24px, #f0f0f0 25px);
                    background-size: 100% 25px;
                    margin: 0;
                    padding: 20px;
                }
                .container {
                    background: white;
                    padding: 20px;
                    border: 1px solid #ccc;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 30px;
                    border-bottom: 2px solid #333;
                    padding-bottom: 10px;
                }
                .section {
                    margin-bottom: 25px;
                    border: 1px solid #999;
                    background: white;
                }
                .section-title {
                    background: #333;
                    color: white;
                    padding: 8px 12px;
                    font-weight: bold;
                    border-bottom: 1px solid #999;
                }
                .row-line {
                    display: flex;
                    border-bottom: 1px solid #ddd;
                    min-height: 25px;
                    align-items: center;
                }
                .row-line:last-child {
                    border-bottom: none;
                }
                .field-label {
                    flex: 0 0 200px;
                    padding: 5px 10px;
                    background: #f8f9fa;
                    border-right: 1px solid #ddd;
                    font-weight: bold;
                }
                .field-value {
                    flex: 1;
                    padding: 5px 10px;
                    min-height: 25px;
                }
                .sub-section {
                    margin-left: 20px;
                    border-left: 2px solid #666;
                }
                .checkbox-field {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                .checkbox-mark {
                    width: 12px;
                    height: 12px;
                    border: 1px solid #333;
                    display: inline-block;
                    text-align: center;
                    line-height: 10px;
                    font-size: 10px;
                }
                .checked {
                    background: #333;
                    color: white;
                }
                .footer {
                    text-align: center;
                    margin-top: 30px;
                    padding-top: 10px;
                    border-top: 1px solid #999;
                    font-size: 10px;
                    color: #666;
                }
                @media print {
                    body { 
                        background: linear-gradient(to bottom, white 0%, white 24px, #f0f0f0 25px);
                        background-size: 100% 25px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <!-- Header -->
                <div class="header">
                    <h2 style="margin: 0; font-size: 18px;">CLÍNICA GONZÁLEZ</h2>
                    <h3 style="margin: 5px 0; font-size: 14px;">HISTORIAL DE ANEMIA Y ANTECEDENTES MÉDICOS</h3>
                    <p style="margin: 0; font-size: 10px;">
                        90 años cuidando tu salud y la de los tuyos | www.clinicagonzalez.com | WSP: 997584512
                    </p>
                </div>

                <!-- Información del Paciente -->
                <div class="section">
                    <div class="section-title">INFORMACIÓN DEL PACIENTE</div>
                    <div class="row-line">
                        <div class="field-label">Nombre completo:</div>
                        <div class="field-value">' . htmlspecialchars($historial['nombre_paciente']) . '</div>
                    </div>
                    <div class="row-line">
                        <div class="field-label">DNI:</div>
                        <div class="field-value">' . htmlspecialchars($historial['dni']) . '</div>
                    </div>
                    <div class="row-line">
                        <div class="field-label">Historia Clínica ID:</div>
                        <div class="field-value">' . htmlspecialchars($historial['historia_clinica_id']) . '</div>
                    </div>
                    <div class="row-line">
                        <div class="field-label">Fecha de creación:</div>
                        <div class="field-value">' . date('d/m/Y', strtotime($historial['fecha_creacion'])) . '</div>
                    </div>
                </div>

                <!-- Alergias y Medicación -->
                <div class="section">
                    <div class="section-title">ALERGIAS Y MEDICACIÓN</div>
                    <div class="row-line">
                        <div class="field-label">Alergias conocidas:</div>
                        <div class="field-value">' . $this->formatearTexto($historial['alergias']) . '</div>
                    </div>
                    <div class="row-line">
                        <div class="field-label">Medicación actual:</div>
                        <div class="field-value">' . $this->formatearTexto($historial['medicacion']) . '</div>
                    </div>
                </div>

                <!-- Enfermedades Crónicas -->
                <div class="section">
                    <div class="section-title">ENFERMEDADES CRÓNICAS Y ANTECEDENTES</div>
                    <div class="row-line">
                        <div class="field-label">Enfermedades pulmonares:</div>
                        <div class="field-value">' . $this->formatearTexto($historial['enfermedades_pulmonares']) . '</div>
                    </div>
                    <div class="row-line">
                        <div class="field-label">Enfermedades cardíacas:</div>
                        <div class="field-value">' . $this->formatearTexto($historial['enfermedades_cardiacas']) . '</div>
                    </div>
                    <div class="row-line">
                        <div class="field-label">Enfermedades neurológicas:</div>
                        <div class="field-value">' . $this->formatearTexto($historial['enfermedades_neurologicas']) . '</div>
                    </div>
                    <div class="row-line">
                        <div class="field-label">Enfermedades hepáticas:</div>
                        <div class="field-value">' . $this->formatearTexto($historial['enfermedades_hepaticas']) . '</div>
                    </div>
                    <div class="row-line">
                        <div class="field-label">Enfermedades renales:</div>
                        <div class="field-value">' . $this->formatearTexto($historial['enfermedades_renales']) . '</div>
                    </div>
                    <div class="row-line">
                        <div class="field-label">Enfermedades endocrinas:</div>
                        <div class="field-value">' . $this->formatearTexto($historial['enfermedades_endocrinas']) . '</div>
                    </div>
                    <div class="row-line">
                        <div class="field-label">Otras enfermedades:</div>
                        <div class="field-value">' . $this->formatearTexto($historial['otras_enfermedades']) . '</div>
                    </div>
                </div>

                <!-- Factores de Riesgo -->
                <div class="section">
                    <div class="section-title">FACTORES DE RIESGO Y ANTECEDENTES QUIRÚRGICOS</div>
                    <div class="row-line">
                        <div class="field-label">Antecedentes quirúrgicos:</div>
                        <div class="field-value">' . $this->formatearTexto($historial['ha_sido_operado']) . '</div>
                    </div>
                    <div class="row-line">
                        <div class="field-label">Factores de riesgo:</div>
                        <div class="field-value">
                            <div class="checkbox-field">
                                <span class="checkbox-mark ' . ($historial['ha_tenido_tumor'] ? 'checked' : '') . '">' . ($historial['ha_tenido_tumor'] ? '✓' : '') . '</span>
                                Ha tenido tumor o cáncer
                            </div>
                            <div class="checkbox-field">
                                <span class="checkbox-mark ' . ($historial['ha_tenido_hemorragia'] ? 'checked' : '') . '">' . ($historial['ha_tenido_hemorragia'] ? '✓' : '') . '</span>
                                Ha tenido hemorragias importantes
                            </div>
                            <div class="checkbox-field">
                                <span class="checkbox-mark ' . ($historial['fuma'] ? 'checked' : '') . '">' . ($historial['fuma'] ? '✓' : '') . '</span>
                                Fuma actualmente
                            </div>
                            <div class="checkbox-field">
                                <span class="checkbox-mark ' . ($historial['toma_anticonceptivos'] ? 'checked' : '') . '">' . ($historial['toma_anticonceptivos'] ? '✓' : '') . '</span>
                                Toma anticonceptivos
                            </div>
                        </div>
                    </div>
                    <div class="row-line">
                        <div class="field-label">Frecuencia de fumar:</div>
                        <div class="field-value">' . $this->formatearTexto($historial['frecuencia_fuma']) . '</div>
                    </div>
                </div>

                <!-- Estado Reproductivo -->
                ' . $this->generarSeccionReproductivo($historial) . '

                <!-- Firma y Fecha -->
                <div class="section">
                    <div class="section-title">FIRMA Y FECHA</div>
                    <div class="row-line" style="min-height: 60px;">
                        <div class="field-label">Firma del médico:</div>
                        <div class="field-value"></div>
                    </div>
                    <div class="row-line">
                        <div class="field-label">Fecha de evaluación:</div>
                        <div class="field-value">_________________________</div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="footer">
                    <p><strong>Clínica González</strong> - 90 años cuidando tu salud y la de los tuyos</p>
                    <p>www.clinicagonzalez.com | WhatsApp: 997584512</p>
                    <p>Documento generado el: ' . date('d/m/Y H:i') . '</p>
                </div>
            </div>
        </body>
        </html>';

        return $html;
    }

    private function formatearTexto($texto)
    {
        if (empty($texto)) {
            return '<span style="color: #999; font-style: italic;">No registrado</span>';
        }
        return nl2br(htmlspecialchars($texto));
    }

    private function generarSeccionReproductivo($historial)
    {
        if (!$historial['esta_embarazada'] && !$historial['periodo_lactancia']) {
            return '';
        }
        
        $html = '
        <div class="section">
            <div class="section-title">ESTADO REPRODUCTIVO</div>';
        
        if ($historial['esta_embarazada']) {
            $html .= '
            <div class="row-line">
                <div class="field-label">Embarazo actual:</div>
                <div class="field-value">
                    <div class="checkbox-field">
                        <span class="checkbox-mark checked">✓</span>
                        Está embarazada actualmente
                    </div>
                    ' . ($historial['semanas_embarazo'] ? '<div><strong>Semanas de gestación:</strong> ' . $historial['semanas_embarazo'] . ' semanas</div>' : '') . '
                </div>
            </div>';
        }
        
        if ($historial['periodo_lactancia']) {
            $html .= '
            <div class="row-line">
                <div class="field-label">Periodo de lactancia:</div>
                <div class="field-value">
                    <div class="checkbox-field">
                        <span class="checkbox-mark checked">✓</span>
                        En período de lactancia
                    </div>
                </div>
            </div>';
        }
        
        $html .= '
        </div>';
        
        return $html;
    }
}
?>