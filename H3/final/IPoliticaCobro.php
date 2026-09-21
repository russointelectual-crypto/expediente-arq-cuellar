<?php
declare(strict_types=1);

require_once __DIR__ . '/DatosCobro.php';

interface IPoliticaCobro
{
    public function nombre(): string;

    public function preparar(DatosCobro $datos): string;

    public function cobrar(DatosCobro $datos): string;
}
