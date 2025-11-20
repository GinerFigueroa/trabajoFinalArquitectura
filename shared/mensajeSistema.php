<?php
/**
 * Patrón: Service Layer / Utility
 * Responsabilidad: Encapsular la lógica de notificación y redirección del sistema.
 */
class mensajeSistema
{

    /**
     * Patrón: Strategy (Presentación Dinámica)
     * El comportamiento visual (colores, íconos) cambia basado en el parámetro $tipo.
     */
    public function mensajeSistemaShow($mensaje, $ruta, $tipo = "error")
    {
        $suceso = ($tipo === "success");
        $icono = $suceso ? "bi-check-circle-fill" : "bi-exclamation-circle-fill";
        $colorHeader = $suceso ? "bg-success" : "bg-danger";
        $colorBoton = $suceso ? "btn-success" : "btn-danger";
        $titulo = $suceso ? "¡Éxito!" : "¡Error!";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Mensajes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="modal fade show d-block" tabindex="-1" aria-labelledby="modalMensaje" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header <?php echo $colorHeader; ?> text-white">
                    <h5 class="modal-title">
                        <i class="bi <?php echo $icono; ?> me-2"></i>
                        <?php echo $titulo; ?>
                    </h5>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i class="bi <?php echo $suceso ? 'bi-check-circle' : 'bi-x-circle'; ?> display-4 <?php echo $suceso ? 'text-success' : 'text-danger'; ?>"></i>
                    </div>
                    <h5 class="mb-3"><?php echo $mensaje; ?></h5>
                    <p class="text-muted">Será redirigido automáticamente...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn <?php echo $colorBoton; ?> w-100" onclick="redirigir()">
                        <i class="bi bi-check-lg me-2"></i>Aceptar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function redirigir() {
            window.location.href = "<?php echo $ruta; ?>";
        }
        
        // Redirección automática después de 3 segundos
        setTimeout(function() {
            redirigir();
        }, 3000);
        
        // Mostrar modal automáticamente
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.querySelector('.modal'));
            modal.show();
        });
    </script>
</body>
</html>
<?php
    }
}
?>
