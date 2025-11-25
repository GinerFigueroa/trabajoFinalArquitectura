<?php

require_once('../../../../../dompdf/autoload.inc.php');
use Dompdf\Dompdf;

// Patrón: STRATEGY 💡 (Definición de la interfaz)
interface FormatoTextoStrategy {
    // Atributo: Método `formatear`
    public function formatear(string $texto): string;
}

// Patrón: STRATEGY Concreta 1 (Formato de texto para PDF)
class FormatoTextoPDFStrategy implements FormatoTextoStrategy {
    // Método: `formatear`
    public function formatear(string $texto): string {
        if (empty($texto)) {
            return '<span style="color: #999; font-style: italic;">No registrado</span>';
        }
        // Método: Aplica formato HTML seguro y saltos de línea
        return nl2br(htmlspecialchars($texto)); 
    }
}

/**
 * Patrón: TEMPLATE METHOD 🧱
 * Define el esqueleto del algoritmo de generación de HTML.
 */
class formHistorialAnemiaPDF
{
    // Atributo: `$formatoStrategy` (Contexto del Strategy)
    private $formatoStrategy;

    // Método: Constructor (Inyecta la Strategy por defecto)
    public function __construct() {
        // Inicialización de la Strategy por defecto
        $this->formatoStrategy = new FormatoTextoPDFStrategy();
    }

    // Método: `generarPDFShow` (Método de Alto Nivel)
    public function generarPDFShow(array $historial)
    {
        // 1. TEMPLATE METHOD: Paso 1 - Generar HTML (Método Abstracto / Subclase Hook)
        $html = $this->generarHtmlHistorial($historial); // Abstracto: Delegado

        // 2. TEMPLATE METHOD: Paso 2 - Renderizar PDF (Método Concreto Final)
        $this->renderizarDompdf($historial, $html); // Concreto: Fijo
    }

    // Método: `renderizarDompdf` (Método del Template - Fijo / Final)
    private function renderizarDompdf(array $historial, string $html)
    {
        // Atributo: `$dompdf`
        $dompdf = new Dompdf();
        
        // Método: `loadHtml`
        $dompdf->loadHtml($html, 'UTF-8');
        // Método: `setPaper`
        $dompdf->setPaper('A4', 'portrait');
        // Método: `render`
        $dompdf->render();

        // Atributo: `$nombreArchivo`
        $nombreArchivo = "Historial-Anemia-" . $historial['nombre_paciente'] . "-" . date('Y-m-d') . ".pdf";
        // Método: `stream`
        $dompdf->stream($nombreArchivo, ["Attachment" => false]);
    }

    // Método: `generarHtmlHistorial` (Método del Template - Core del contenido)
    private function generarHtmlHistorial(array $historial): string
    {
        // Usa la Strategy para formatear los datos (Punto de variación)
        // Método: `formatear` (uso de la Strategy)
        $alergias = $this->formatoStrategy->formatear($historial['alergias']);
        $medicacion = $this->formatoStrategy->formatear($historial['medicacion']);
        $pulmonares = $this->formatoStrategy->formatear($historial['enfermedades_pulmonares']);
        // ... y así sucesivamente para todos los campos de texto

        $html = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Historial de Anemia - ' . htmlspecialchars($historial['nombre_paciente']) . '</title>
            <style>
                /* ... Estilos CSS para el PDF ... */
                body { 
                    font-family: Arial, sans-serif; 
                    font-size: 12px; 
                    /* ... */
                }
                /* ... otros estilos ... */
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2 style="margin: 0; font-size: 18px;">CLÍNICA GONZÁLEZ</h2>
                    <h3 style="margin: 5px 0; font-size: 14px;">HISTORIAL DE ANEMIA Y ANTECEDENTES MÉDICOS</h3>
                    <p style="margin: 0; font-size: 10px;">
                        90 años cuidando tu salud y la de los tuyos | www.clinicagonzalez.com | WSP: 997584512
                    </p>
                </div>

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

                <div class="section">
                    <div class="section-title">ALERGIAS Y MEDICACIÓN</div>
                    <div class="row-line">
                        <div class="field-label">Alergias conocidas:</div>
                        <div class="field-value">' . $alergias . '</div>
                    </div>
                    <div class="row-line">
                        <div class="field-label">Medicación actual:</div>
                        <div class="field-value">' . $medicacion . '</div>
                    </div>
                </div>

                <div class="section">
                    <div class="section-title">ENFERMEDADES CRÓNICAS Y ANTECEDENTES</div>
                    <div class="row-line">
                        <div class="field-label">Enfermedades pulmonares:</div>
                        <div class="field-value">' . $pulmonares . '</div>
                    </div>
                    </div>

                <div class="section">
                    <div class="section-title">FACTORES DE RIESGO Y ANTECEDENTES QUIRÚRGICOS</div>
                    <div class="row-line">
                        <div class="field-label">Antecedentes quirúrgicos:</div>
                        <div class="field-value">' . $this->formatoStrategy->formatear($historial['ha_sido_operado']) . '</div>
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
                        <div class="field-value">' . $this->formatoStrategy->formatear($historial['frecuencia_fuma']) . '</div>
                    </div>
                </div>

                ' . $this->generarSeccionReproductivo($historial) . '

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

    // Método: `generarSeccionReproductivo`
    private function generarSeccionReproductivo(array $historial): string
    {
        // Atributo: `esta_embarazada`, `periodo_lactancia`
        if (!$historial['esta_embarazada'] && !$historial['periodo_lactancia']) {
            return '';
        }
        
        // ... (Cuerpo de la función que genera la sección HTML) ...
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