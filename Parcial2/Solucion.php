<?php
declare(strict_types=1);

// Solucion: <Richard Cuellar Rojas>
// Fuerza Andina - Situacion 1: Observer.


// Contrato que deben cumplir todos los interesados en el evento.
interface ObservadorVencimiento
{
    public function actualizar(string $nombreSocio): void;
}

// Suscriptor concreto 1. Simula un aviso; no envia mensajes reales.
class AvisoWhatsApp implements ObservadorVencimiento
{
    public function actualizar(string $nombreSocio): void
    {
        echo "[WhatsApp simulado] {$nombreSocio}, tu membresia ha vencido." . PHP_EOL;
    }
}

// Suscriptor concreto 2. Conserva el registro en memoria durante la ejecucion.
class RegistroVencidos implements ObservadorVencimiento
{
    private array $socios = [];

    public function actualizar(string $nombreSocio): void
    {
        $this->socios[] = $nombreSocio;
        echo "[Registro] Se agrego a {$nombreSocio} a la lista de vencidos." . PHP_EOL;
    }

    public function obtenerSocios(): array
    {
        return $this->socios;
    }
}

// Sujeto: conoce el contrato, pero no las clases concretas de sus suscriptores.
class ModuloSocios
{
    /** @var ObservadorVencimiento[] */
    private array $observadores = [];

    public function suscribir(ObservadorVencimiento $observador): void
    {
        $this->observadores[spl_object_id($observador)] = $observador;
    }

    public function desuscribir(ObservadorVencimiento $observador): void
    {
        unset($this->observadores[spl_object_id($observador)]);
    }

    // Se invoca cuando el sistema detecta que una membresia ha vencido.
    public function vencerMembresia(string $nombreSocio): void
    {
        echo "[Fuerza Andina] Membresia vencida: {$nombreSocio}." . PHP_EOL;
        foreach ($this->observadores as $observador) {
            $observador->actualizar($nombreSocio);
        }
    }
}

// Ejemplo de uso: las dependencias concretas se conectan fuera del sujeto.
$moduloSocios = new ModuloSocios();
$whatsApp = new AvisoWhatsApp();
$registro = new RegistroVencidos();

$moduloSocios->suscribir($whatsApp);
$moduloSocios->suscribir($registro);
$moduloSocios->vencerMembresia('Ana Perez');

echo 'Socios vencidos: ' . implode(', ', $registro->obtenerSocios()) . PHP_EOL;
