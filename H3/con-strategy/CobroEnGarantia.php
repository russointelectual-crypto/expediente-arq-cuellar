<?php
declare(strict_types=1);

require_once __DIR__ . '/IPoliticaCobro.php';

class CobroEnGarantia implements IPoliticaCobro
{
    public function calcular(DatosCobro $datos): int
    {
        // Supuesto: la cobertura total ya fue aprobada por el taller.
        return 0;
    }
}
