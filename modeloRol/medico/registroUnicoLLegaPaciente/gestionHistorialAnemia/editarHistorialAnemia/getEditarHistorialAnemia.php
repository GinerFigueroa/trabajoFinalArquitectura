<?php

session_start();
// Incluimos los componentes
include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlEditarHistorialAnemia.php');

// Atributo: `$objMensaje`
$objMensaje = new mensajeSistema();

// Patrón: COMMAND (Interfaz) 💡
interface Comando {
    // Método: `ejecutar` (método abstracto)
    public function ejecutar(): void;
}

// Patrón: FACTORY METHOD (Fábrica de Comandos) 🏭
class ComandoFactory {
    // Método: `crearComando` (método de fábrica simple)
    public static function crearComando(string $accion, array $datosForm): ?Comando {
        if ($accion === 'btnEditar') {
            // Devuelve una instancia del comando concreto
            // Atributo: `$datosForm`
            return new EditarHistorialComando($datosForm);
        }
        return null;
    }
}

// Patrón: COMMAND (Implementación Concreta) 🛠️
class EditarHistorialComando implements Comando {
    // Atributo: `$datos` (Datos del formulario)
    private $datos;
    // Atributo: `$receptor` (Controlador)
    private $receptor;

    // Método: Constructor (Atributos: $datos, $receptor)
    public function __construct(array $datos) {
        $this->datos = $datos;
        // Inicializa el Receptor que ejecutará la lógica de negocio real
        $this->receptor = new controlEditarHistorialAnemia();
    }

    // Método: `ejecutar` (Llama al Receptor)
    public function ejecutar(): void {
        $this->receptor->procesarEdicion($this->datos);
    }
}


if (isset($_POST['btnEditar'])) {
    // Uso del Factory Method para crear el comando
    // Atributo: `$comando`
    $comando = ComandoFactory::crearComando('btnEditar', $_POST);
    
    if ($comando) {
        // Ejecución del Comando
        $comando->ejecutar();
    } else {
        $objMensaje->mensajeSistemaShow('Comando no encontrado.', '../indexHistorialAnemia.php', 'error');
    }
} else {
    $objMensaje->mensajeSistemaShow('Acceso denegado.', '../indexHistorialAnemia.php', 'error');
}
?>