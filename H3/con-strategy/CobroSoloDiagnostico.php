<?php
declare(strict_types=1);

require_once __DIR__ . '/IPoliticaCobro.php';

class CobroSoloDiagnostico implements IPoliticaCobro
{
    public function calcular(DatosCobro $datos): int
    {
        return $datos->diagnostico;
    }
}
