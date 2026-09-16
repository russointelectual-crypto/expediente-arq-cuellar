<?php
declare(strict_types=1);

require_once __DIR__ . '/DatosCobro.php';

interface IPoliticaCobro
{
    public function calcular(DatosCobro $datos): int;
}
