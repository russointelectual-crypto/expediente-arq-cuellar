<?php
declare(strict_types=1);

require_once __DIR__ . '/OrdenDeTrabajo.php';
require_once __DIR__ . '/IObservadorOrden.php';

final class OrdenObservable extends OrdenDeTrabajo
{
    /** @var array<int, IObservadorOrden> */
    private array $observadores = [];

    public function suscribir(IObservadorOrden $observador): void
    {
        $this->observadores[spl_object_id($observador)] = $observador;
    }

    public function desuscribir(IObservadorOrden $observador): void
    {
        unset($this->observadores[spl_object_id($observador)]);
    }

    public function aplicar(string $evento): void
    {
        $estadoAnterior = $this->estadoActual();
        parent::aplicar($evento);

        $cambio = new CambioEstado(
            $this->numeroRecibo,
            $this->cliente->nombreCompleto,
            $this->cliente->celularPrincipal,
            $estadoAnterior,
            $this->estadoActual()
        );

        foreach ($this->observadores as $observador) {
            $observador->actualizar($cambio);
        }
    }
}
