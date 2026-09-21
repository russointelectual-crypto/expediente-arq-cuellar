<?php
declare(strict_types=1);

require_once __DIR__ . '/IObservadorOrden.php';

final class BitacoraOrden implements IObservadorOrden
{
    /** @var list<string> */
    private array $registros = [];

    public function actualizar(CambioEstado $cambio): void
    {
        $registro = $cambio->numeroRecibo . ': ' . $cambio->estadoAnterior
            . ' -> ' . $cambio->estadoActual;
        $this->registros[] = $registro;
        echo '[BITACORA] ' . $registro . "\n";
    }

    /** @return list<string> */
    public function registros(): array
    {
        return $this->registros;
    }
}
