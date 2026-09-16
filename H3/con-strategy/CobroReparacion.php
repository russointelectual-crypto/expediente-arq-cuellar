<?php
declare(strict_types=1);

require_once __DIR__ . '/IPoliticaCobro.php';

class CobroReparacion implements IPoliticaCobro
{
    public function calcular(DatosCobro $datos): int
    {
        // Supuesto del ejemplo: el diagnostico esta incluido en la reparacion.
        return $datos->manoDeObra + $datos->repuestos;
    }
}
