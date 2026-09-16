<?php
declare(strict_types=1);

require_once __DIR__ . '/../base/OrdenDeTrabajo.php';
require_once __DIR__ . '/IObservadorOrden.php';

class OrdenObservable extends OrdenDeTrabajo
{
    /** @var array<int, IObservadorOrden> */
    private array $observadores = [];

    public function suscribir(IObservadorOrden $observador): void
    {
        // La misma instancia no se registra dos veces.
        $this->observadores[spl_object_id($observador)] = $observador;
    }

    public function desuscribir(IObservadorOrden $observador): void
    {
        unset($this->observadores[spl_object_id($observador)]);
    }

    public function aplicar(string $evento): void
    {
        $anterior = $this->estadoActual();
        // Se conservan las validaciones originales. Si falla, no publica nada.
        parent::aplicar($evento);
        $cambio = new CambioEstado(
            $this->numeroRecibo,
            $this->cliente->nombreCompleto,
            $this->cliente->celularPrincipal,
            $anterior,
            $this->estadoActual()
        );
        foreach ($this->observadores as $observador) {
            $observador->actualizar($cambio);
        }
    }
}
