<?php
session_start();

require_once('../../../../../dompdf/autoload.inc.php');
use Dompdf\Dompdf;

include_once("../../../../../modelo/InternadoPDFseguimientoDAO.php");
include_once("../../../../../shared/mensajeSistema.php");

class controlInternadoPDF
{
    private $objDAO;
    private $objMensaje;

    public function __construct() {
        $this->objDAO = new InternadoPDFseguimientoDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function procesarSolicitud()
    {
        $action = $_GET['action'] ?? '';
        $idInternado = $_GET['id'] ?? null;

        if (!$idInternado || !is_numeric($idInternado)) {
            $this->objMensaje->mensajeSistemaShow("ID de internado no válido.", "../indexGestionInternados.php", "error");
            return;
        }

        switch ($action) {
            case 'completo':
                $this->generarPDFCompleto($idInternado);
                break;
            case 'resumen':
                $this->generarResumenClinico($idInternado);
                break;
            case 'seguimientos':
                $this->generarSoloSeguimientos($idInternado);
                break;
            default:
                $this->objMensaje->mensajeSistemaShow("Acción no válida.", "../indexGestionInternados.php", "error");
        }
    }

    private function generarPDFCompleto($idInternado)
    {
        $datos = $this->objDAO->obtenerDatosCombinados($idInternado);
        
        if (!$datos || !$datos['internado']) {
            $this->objMensaje->mensajeSistemaShow("No se encontró el internado especificado.", "../indexGestionInternados.php", "error");
            return;
        }

        $html = $this->generarHTMLCompleto($datos);
        $this->renderizarPDF($html, "Internado_Completo_" . $datos['internado']['id_internado']);
    }

    private function generarResumenClinico($idInternado)
    {
        $datos = $this->objDAO->obtenerDatosCombinados($idInternado);
        
        if (!$datos || !$datos['internado']) {
            $this->objMensaje->mensajeSistemaShow("No se encontró el internado especificado.", "../indexGestionInternados.php", "error");
            return;
        }

        $html = $this->generarHTMLResumen($datos);
        $this->renderizarPDF($html, "Resumen_Clinico_" . $datos['internado']['id_internado']);
    }

    private function generarSoloSeguimientos($idInternado)
    {
        $datos = $this->objDAO->obtenerDatosCombinados($idInternado);
        
        if (!$datos || !$datos['internado']) {
            $this->objMensaje->mensajeSistemaShow("No se encontró el internado especificado.", "../indexGestionInternados.php", "error");
            return;
        }

        $html = $this->generarHTMLSoloSeguimientos($datos);
        $this->renderizarPDF($html, "Seguimientos_" . $datos['internado']['id_internado']);
    }

    private function generarHTMLCompleto($datos)
    {
        $internado = $datos['internado'];
        $seguimientos = $datos['seguimientos'];
        $infoPaciente = $datos['info_paciente'];
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Historial Completo - Internado #<?php echo htmlspecialchars($internado['id_internado']); ?></title>
            <style>
                body { 
                    font-family: 'DejaVu Sans', Arial, sans-serif; 
                    font-size: 11px; 
                    line-height: 1.3;
                    color: #333;
                    margin: 0;
                    padding: 15px;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 20px;
                    border-bottom: 3px double #1e3c72;
                    padding-bottom: 10px;
                }
                .hospital-title {
                    color: #1e3c72;
                    font-size: 18px;
                    font-weight: bold;
                    margin-bottom: 3px;
                }
                .section {
                    margin-bottom: 15px;
                    page-break-inside: avoid;
                }
                .section-title {
                    background-color: #1e3c72;
                    color: white;
                    padding: 6px 10px;
                    font-weight: bold;
                    margin-bottom: 8px;
                    border-radius: 3px;
                    font-size: 12px;
                }
                .patient-info {
                    border: 1px solid #ddd;
                    padding: 10px;
                    margin-bottom: 10px;
                    border-radius: 4px;
                }
                .info-row {
                    margin-bottom: 5px;
                    display: flex;
                }
                .info-label {
                    font-weight: bold;
                    min-width: 140px;
                    color: #1e3c72;
                }
                .seguimiento-item {
                    border-left: 3px solid #28a745;
                    padding-left: 8px;
                    margin-bottom: 8px;
                    background-color: #f8fff8;
                    padding: 8px;
                }
                .footer {
                    margin-top: 20px;
                    text-align: center;
                    font-size: 9px;
                    color: #666;
                    border-top: 1px solid #ddd;
                    padding-top: 8px;
                }
            </style>
        </head>
        <body>
            <!-- Encabezado -->
            <div class="header">
                <div class="hospital-title">CLÍNICA GONZÁLEZ</div>
                <div style="font-size: 12px; color: #2a5298; margin-bottom: 5px;">
                    Servicio de Internamiento
                </div>
                <div style="margin-top: 8px; font-size: 14px; font-weight: bold;">
                    HISTORIAL MÉDICO COMPLETO - INTERNADO
                </div>
            </div>

            <!-- Información del Paciente -->
            <div class="section">
                <div class="section-title">INFORMACIÓN DEL PACIENTE</div>
                <div class="patient-info">
                    <div class="info-row">
                        <div class="info-label">Paciente:</div>
                        <div><?php echo htmlspecialchars($internado['nombre_completo_paciente'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">DNI:</div>
                        <div><?php echo htmlspecialchars($internado['dni_paciente'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Fecha Nacimiento:</div>
                        <div><?php echo $internado['fecha_nacimiento'] ? date('d/m/Y', strtotime($internado['fecha_nacimiento'])) : 'N/A'; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Sexo:</div>
                        <div><?php echo htmlspecialchars($internado['sexo'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Teléfono:</div>
                        <div><?php echo htmlspecialchars($internado['telefono'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Domicilio:</div>
                        <div><?php echo htmlspecialchars($internado['domicilio'] ?? 'N/A'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Información del Internado -->
            <div class="section">
                <div class="section-title">DATOS DEL INTERNADO</div>
                <div class="patient-info">
                    <div class="info-row">
                        <div class="info-label">ID Internado:</div>
                        <div>#<?php echo htmlspecialchars($internado['id_internado']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Fecha Ingreso:</div>
                        <div><?php echo date('d/m/Y H:i', strtotime($internado['fecha_ingreso'])); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Fecha Alta:</div>
                        <div><?php echo $internado['fecha_alta'] ? date('d/m/Y H:i', strtotime($internado['fecha_alta'])) : 'Pendiente'; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Habitación:</div>
                        <div><?php echo htmlspecialchars($internado['habitacion_numero'] ?? 'N/A'); ?> (Piso <?php echo htmlspecialchars($internado['habitacion_piso'] ?? 'N/A'); ?>)</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Médico Tratante:</div>
                        <div><?php echo htmlspecialchars($internado['nombre_medico'] ?? 'No asignado'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Especialidad:</div>
                        <div><?php echo htmlspecialchars($internado['especialidad_medico'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Estado:</div>
                        <div><?php echo htmlspecialchars($internado['estado']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Diagnóstico y Observaciones -->
            <div class="section">
                <div class="section-title">DIAGNÓSTICO Y OBSERVACIONES</div>
                <div class="patient-info">
                    <div class="info-row">
                        <div class="info-label">Diagnóstico de Ingreso:</div>
                        <div><?php echo htmlspecialchars($internado['diagnostico_ingreso'] ?? 'No especificado'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Observaciones:</div>
                        <div><?php echo htmlspecialchars($internado['observaciones'] ?? 'No hay observaciones'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Evolución y Seguimientos -->
            <?php if (!empty($seguimientos)): ?>
            <div class="section">
                <div class="section-title">EVOLUCIÓN CLÍNICA Y SEGUIMIENTOS (<?php echo count($seguimientos); ?> registros)</div>
                <?php foreach ($seguimientos as $index => $seguimiento): ?>
                    <div class="seguimiento-item">
                        <div style="font-weight: bold; color: #1e3c72; margin-bottom: 5px;">
                            Fecha: <?php echo date('d/m/Y H:i', strtotime($seguimiento['fecha'])); ?>
                            <?php if ($seguimiento['nombre_medico']): ?>
                                - Médico: <?php echo htmlspecialchars($seguimiento['nombre_medico']); ?>
                            <?php endif; ?>
                        </div>
                        <div style="margin-bottom: 5px;">
                            <strong>Evolución:</strong> <?php echo htmlspecialchars($seguimiento['evolucion'] ?? 'Sin descripción'); ?>
                        </div>
                        <?php if ($seguimiento['tratamiento']): ?>
                        <div>
                            <strong>Tratamiento:</strong> <?php echo htmlspecialchars($seguimiento['tratamiento']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Resumen -->
            <div class="section">
                <div class="section-title">RESUMEN</div>
                <div class="patient-info">
                    <div class="info-row">
                        <div class="info-label">Días de Internamiento:</div>
                        <div><?php echo $datos['dias_internado']; ?> días</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Total Seguimientos:</div>
                        <div><?php echo count($seguimientos); ?> registros</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Estado Actual:</div>
                        <div><?php echo htmlspecialchars($internado['estado']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Pie de Página -->
            <div class="footer">
                <div>Documento generado el: <?php echo date('d/m/Y H:i'); ?></div>
                <div>Clínica González - Servicio de Internamiento</div>
                <div>Documento confidencial - Prohibida su reproducción no autorizada</div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    // ... (métodos similares para generarHTMLResumen y generarHTMLSoloSeguimientos)

    private function renderizarPDF($html, $nombreArchivo)
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nombreArchivo = preg_replace('/[^a-zA-Z0-9]/', '_', $nombreArchivo) . "_" . date('Ymd_His') . ".pdf";
        
        $dompdf->stream($nombreArchivo, [
            "Attachment" => true,
            "compress" => true
        ]);
    }
}

// Ejecutar el controlador
$objControl = new controlInternadoPDF();
$objControl->procesarSolicitud();
?>