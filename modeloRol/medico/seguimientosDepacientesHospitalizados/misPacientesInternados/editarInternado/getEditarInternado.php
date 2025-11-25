
<?php
// Directorio: /controlador/internado/getEditarInternado.php

session_start();
include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlEditarInternado.php');

// Patrón: Builder Implícito (Recolección y Mapeo de datos)
class InternadoDataBuilder {
    // Atributos: Los datos individuales que se recolectan del POST
    // Método: Construye y retorna el array de datos
    public static function buildInternadoData(): array {
        return [
            'idInternado' => $_POST['idInternado'] ?? null,
            'idHabitacionAnterior' => $_POST['idHabitacionAnterior'] ?? null,
            'idHabitacion' => $_POST['idHabitacion'] ?? null,
            'idMedico' => $_POST['idMedico'] ?? null,
            'fechaAlta' => $_POST['fechaAlta'] ?? null,
            'diagnostico' => $_POST['diagnostico'] ?? '',
            'observaciones' => $_POST['observaciones'] ?? '',
            'estado' => $_POST['estado'] ?? '',
            // Atributo adicional de sesión
            'modificadoPor' => $_SESSION['id_usuario'] ?? 1 
        ];
    }
}

$objMensaje = new mensajeSistema();

if (isset($_POST['btnEditar'])) {
    // Builder: Recolección y construcción de los datos
    $internadoData = InternadoDataBuilder::buildInternadoData();
    
    // Mediator (Control): Se invoca al control
    $objControl = new controlEditarInternado();
    
    // Command: El control actúa como Invoker del comando
    // Atributo: `$internadoData` (Datos construidos)
    // Método: `ejecutarComandoEditarInternado` (Invoca la acción)
    $objControl->ejecutarComandoEditarInternado($internadoData);
} else {
    // Acceso directo denegado
    $objMensaje->mensajeSistemaShow("Acceso denegado.", "../indexGestionInternados.php", "error");
}
?>