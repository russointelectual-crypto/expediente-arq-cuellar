<?php
declare(strict_types=1);

require_once __DIR__ . '/ReciboDecorador.php';

final class ConRecomendaciones extends ReciboDecorador
{
    public function __construct(IRecibo $recibo, private string $recomendaciones)
    {
        parent::__construct($recibo);
        if (trim($recomendaciones) === '') {
            throw new InvalidArgumentException('Las recomendaciones no pueden estar vacias.');
        }
    }

    public function generar(): string
    {
        return parent::generar() . 'Recomendaciones: ' . $this->recomendaciones . "\n";
    }
}
