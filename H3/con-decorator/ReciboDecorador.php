<?php
declare(strict_types=1);

require_once __DIR__ . '/IRecibo.php';

abstract class ReciboDecorador implements IRecibo
{
    public function __construct(protected IRecibo $recibo)
    {
    }

    public function generar(): string
    {
        return $this->recibo->generar();
    }
}
