<?php
session_start();

require_once('../../../../../dompdf/autoload.inc.php');
use Dompdf\Dompdf;

include_once("../../../../../modelo/HistorialClinicopdfDAO.php");
include_once("../../../../../shared/mensajeSistema.php");

class controlGenerarHistorialPacientePDF
{
    private $objDAO;
    private $objMensaje;

    public function __construct() {
        $this->objDAO = new HistorialClinicoDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function procesarSolicitud()
    {
        $action = $_GET['action'] ?? '';
        $hc_id = $_GET['hc_id'] ?? null;

        if (!$hc_id || !is_numeric($hc_id)) {
            $this->objMensaje->mensajeSistemaShow("ID de historia clínica no válido.", "../indexGenerarHistorialPacientePDF.php", "error");
            return;
        }

        switch ($action) {
            case 'generar':
                $this->generarPDFCompleto($hc_id);
                break;
            case 'preview':
                $this->mostrarVistaPrevia($hc_id);
                break;
            default:
                $this->objMensaje->mensajeSistemaShow("Acción no válida.", "../indexHistorialMedico.php", "error");
        }
    }

    private function generarPDFCompleto($historiaClinicaId)
    {
        $datosHistorial = $this->objDAO->obtenerHistorialCompletoPorHC($historiaClinicaId);
        
        if (!$datosHistorial) {
            $this->objMensaje->mensajeSistemaShow("No se encontró historial clínico para el ID proporcionado.", "../indexHistorialMedico", "error");
            return;
        }

        $html = $this->generarHTMLHistorialCompleto($datosHistorial);
        $this->renderizarPDF($html, $datosHistorial['paciente']['nombre_completo']);
    }

    private function mostrarVistaPrevia($historiaClinicaId)
    {
        $datosHistorial = $this->objDAO->obtenerHistorialCompletoPorHC($historiaClinicaId);
        
        if (!$datosHistorial) {
            $this->objMensaje->mensajeSistemaShow("No se encontró historial clínico para el ID proporcionado.", "../indexHistorialMedico", "error");
            return;
        }

        $this->mostrarHTMLVistaPrevia($datosHistorial);
    }

    private function generarHTMLHistorialCompleto($datos)
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Historial Clínico - <?php echo htmlspecialchars($datos['paciente']['nombre_completo']); ?></title>
            <style>
                body { 
                    font-family: 'DejaVu Sans', Arial, sans-serif; 
                    font-size: 12px; 
                    line-height: 1.4;
                    color: #333;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 25px;
                    border-bottom: 3px double #1e3c72;
                    padding-bottom: 15px;
                }
                .hospital-title {
                    color: #1e3c72;
                    font-size: 20px;
                    font-weight: bold;
                    margin-bottom: 5px;
                }
                .hospital-subtitle {
                    color: #2a5298;
                    font-size: 14px;
                    margin-bottom: 10px;
                }
                .section {
                    margin-bottom: 20px;
                    page-break-inside: avoid;
                }
                .section-title {
                    background-color: #1e3c72;
                    color: white;
                    padding: 8px 12px;
                    font-weight: bold;
                    margin-bottom: 10px;
                    border-radius: 4px;
                }
                .patient-info {
                    border: 1px solid #ddd;
                    padding: 15px;
                    margin-bottom: 15px;
                    border-radius: 5px;
                }
                .info-row {
                    margin-bottom: 8px;
                    display: flex;
                }
                .info-label {
                    font-weight: bold;
                    min-width: 150px;
                    color: #1e3c72;
                }
                .table-medical {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 15px;
                }
                .table-medical th {
                    background-color: #f8f9fa;
                    border: 1px solid #dee2e6;
                    padding: 8px;
                    text-align: left;
                    font-weight: bold;
                }
                .table-medical td {
                    border: 1px solid #dee2e6;
                    padding: 8px;
                    vertical-align: top;
                }
                .signature-area {
                    margin-top: 50px;
                    border-top: 1px solid #333;
                    padding-top: 10px;
                    text-align: center;
                }
                .footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: 10px;
                    color: #666;
                }
                .page-break {
                    page-break-before: always;
                }
                .risk-factor {
                    background-color: #fff3cd;
                    padding: 5px;
                    border-radius: 3px;
                    margin: 2px 0;
                }
            </style>
        </head>
        <body>
            <!-- Encabezado del Hospital -->
            <div class="header">
                <div class="hospital-title">CLÍNICA GONZÁLEZ</div>
                <div class="hospital-subtitle">90 años cuidando tu salud y la de los tuyos</div>
                <div style="font-size: 11px; color: #666;">
                    WSP 997584512 | www.clinicagonzalez.com | 40+ Especialidades
                </div>
                <div style="margin-top: 10px; font-size: 16px; font-weight: bold;">
                    HISTORIAL CLÍNICO COMPLETO
                </div>
            </div>

            <!-- Información del Paciente -->
            <div class="section">
                <div class="section-title">INFORMACIÓN DEL PACIENTE</div>
                <div class="patient-info">
                    <div class="info-row">
                        <div class="info-label">Nombre Completo:</div>
                        <div><?php echo htmlspecialchars($datos['paciente']['nombre_completo']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">DNI:</div>
                        <div><?php echo htmlspecialchars($datos['paciente']['dni']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Historia Clínica N°:</div>
                        <div>HC-<?php echo htmlspecialchars($datos['paciente']['historia_clinica_id']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Fecha Creación HC:</div>
                        <div><?php echo date('d/m/Y', strtotime($datos['paciente']['fecha_creacion'])); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Médico Tratante:</div>
                        <div><?php echo htmlspecialchars($datos['paciente']['nombre_tratante']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Anamnesis/Historial de Anemia -->
            <?php if ($datos['anamnesis']): ?>
            <div class="section">
                <div class="section-title">ANAMNESIS E HISTORIAL MÉDICO</div>
                <div class="patient-info">
                    <?php if ($datos['anamnesis']['alergias']): ?>
                    <div class="info-row">
                        <div class="info-label">Alergias:</div>
                        <div class="risk-factor"><?php echo htmlspecialchars($datos['anamnesis']['alergias']); ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if ($datos['anamnesis']['enfermedades_pulmonares']): ?>
                    <div class="info-row">
                        <div class="info-label">Enf. Pulmonares:</div>
                        <div><?php echo htmlspecialchars($datos['anamnesis']['enfermedades_pulmonares']); ?></div>
                    </div>
                    <?php endif; ?>

                    <!-- Continuar con otros campos de anamnesis... -->

                    <div class="info-row">
                        <div class="info-label">Factor de Riesgo:</div>
                        <div>
                            <?php
                            $factores = [];
                            if ($datos['anamnesis']['ha_tenido_tumor']) $factores[] = 'Tumor';
                            if ($datos['anamnesis']['ha_tenido_hemorragia']) $factores[] = 'Hemorragia';
                            if ($datos['anamnesis']['fuma']) $factores[] = 'Fumador';
                            if ($datos['anamnesis']['esta_embarazada']) $factores[] = 'Embarazada';
                            if ($datos['anamnesis']['periodo_lactancia']) $factores[] = 'Lactancia';
                            echo $factores ? implode(', ', $factores) : 'Ninguno identificado';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Registros Médicos -->
            <?php if ($datos['registros_medicos']): ?>
            <div class="section page-break">
                <div class="section-title">REGISTROS MÉDICOS</div>
                <?php foreach ($datos['registros_medicos'] as $registro): ?>
                <div style="margin-bottom: 20px; border: 1px solid #eee; padding: 15px; border-radius: 5px;">
                    <div style="font-weight: bold; color: #1e3c72; margin-bottom: 10px;">
                        Fecha: <?php echo date('d/m/Y H:i', strtotime($registro['fecha_registro'])); ?>
                    </div>
                    
                    <?php if ($registro['motivo_consulta']): ?>
                    <div style="margin-bottom: 8px;">
                        <strong>Motivo Consulta:</strong> <?php echo htmlspecialchars($registro['motivo_consulta']); ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($registro['enfermedad_actual']): ?>
                    <div style="margin-bottom: 8px;">
                        <strong>Enfermedad Actual:</strong> <?php echo htmlspecialchars($registro['enfermedad_actual']); ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($registro['signos_sintomas']): ?>
                    <div style="margin-bottom: 8px;">
                        <strong>Signos/Síntomas:</strong> <?php echo htmlspecialchars($registro['signos_sintomas']); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Evoluciones Médicas -->
            <?php if ($datos['evoluciones']): ?>
            <div class="section page-break">
                <div class="section-title">EVOLUCIONES MÉDICAS (SOAP)</div>
                <?php foreach ($datos['evoluciones'] as $evolucion): ?>
                <div style="margin-bottom: 25px; border: 1px solid #eee; padding: 15px; border-radius: 5px;">
                    <div style="font-weight: bold; color: #1e3c72; margin-bottom: 15px;">
                        Fecha: <?php echo date('d/m/Y H:i', strtotime($evolucion['fecha_evolucion'])); ?> 
                        | Médico: <?php echo htmlspecialchars($evolucion['nombre_medico']); ?>
                    </div>
                    
                    <table class="table-medical">
                        <tr>
                            <th style="width: 20%;">Subjetivo (S)</th>
                            <td><?php echo nl2br(htmlspecialchars($evolucion['nota_subjetiva'] ?: 'No registrado')); ?></td>
                        </tr>
                        <tr>
                            <th>Objetivo (O)</th>
                            <td><?php echo nl2br(htmlspecialchars($evolucion['nota_objetiva'] ?: 'No registrado')); ?></td>
                        </tr>
                        <tr>
                            <th>Análisis (A)</th>
                            <td><?php echo nl2br(htmlspecialchars($evolucion['analisis'] ?: 'No registrado')); ?></td>
                        </tr>
                        <tr>
                            <th>Plan (P)</th>
                            <td><?php echo nl2br(htmlspecialchars($evolucion['plan_de_accion'] ?: 'No registrado')); ?></td>
                        </tr>
                    </table>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Exámenes y Órdenes -->
            <?php if ($datos['ordenes_examen']): ?>
            <div class="section page-break">
                <div class="section-title">ÓRDENES DE EXAMEN</div>
                <table class="table-medical">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo Examen</th>
                            <th>Indicaciones</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($datos['ordenes_examen'] as $orden): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($orden['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($orden['tipo_examen']); ?></td>
                            <td><?php echo htmlspecialchars($orden['indicaciones'] ?: 'Sin indicaciones específicas'); ?></td>
                            <td>
                                <span style="padding: 2px 6px; border-radius: 3px; font-size: 10px; 
                                    background-color: <?php echo $this->getColorEstado($orden['estado']); ?>; 
                                    color: white;">
                                    <?php echo htmlspecialchars($orden['estado']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Firmas y Validaciones -->
            <div class="section">
                <div class="signature-area">
                    <div style="margin-bottom: 20px;">
                        <strong>VALIDADO POR:</strong>
                    </div>
                    <div style="display: flex; justify-content: space-around; margin-top: 30px;">
                        <div style="text-align: center;">
                            <div style="border-top: 1px solid #333; width: 200px; margin-bottom: 5px;"></div>
                            <div>Médico Tratante</div>
                            <div style="font-size: 10px; color: #666;">Dr. <?php echo htmlspecialchars($datos['paciente']['nombre_tratante']); ?></div>
                        </div>
                        <div style="text-align: center;">
                            <div style="border-top: 1px solid #333; width: 200px; margin-bottom: 5px;"></div>
                            <div>Coordinador Médico</div>
                            <div style="font-size: 10px; color: #666;">Clínica González</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pie de Página -->
            <div class="footer">
                <div>Documento generado el: <?php echo date('d/m/Y H:i'); ?></div>
                <div>Clínica González - Sistema de Gestión de Historias Clínicas</div>
                <div>Este es un documento médico confidencial - Prohibida su reproducción no autorizada</div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    private function getColorEstado($estado)
    {
        switch ($estado) {
            case 'Pendiente': return '#ffc107';
            case 'Realizado': return '#28a745';
            case 'Entregado': return '#17a2b8';
            default: return '#6c757d';
        }
    }

    private function renderizarPDF($html, $nombrePaciente)
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nombreArchivo = "Historial_Clinico_" . preg_replace('/[^a-zA-Z0-9]/', '_', $nombrePaciente) . "_" . date('Ymd_His') . ".pdf";
        
        $dompdf->stream($nombreArchivo, [
            "Attachment" => true,
            "compress" => true
        ]);
    }

    private function mostrarHTMLVistaPrevia($datos)
    {
        // Similar a generarHTMLHistorialCompleto pero para vista web
        $html = $this->generarHTMLHistorialCompleto($datos);
        echo $html;
    }
}

// Ejecutar el controlador
$objControl = new controlGenerarHistorialPacientePDF();
$objControl->procesarSolicitud();
?>