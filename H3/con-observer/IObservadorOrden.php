<?php
declare(strict_types=1);

require_once __DIR__ . '/CambioEstado.php';

interface IObservadorOrden
{
    public function actualizar(CambioEstado $cambio): void;
}
