<?php
declare(strict_types=1);

require_once __DIR__ . '/IPoliticaCobro.php';

class ServicioCaja
{
    public function __construct(private IPoliticaCobro $politica)
    {
    }

    public function cambiarPolitica(IPoliticaCobro $politica): void
    {
        $this->politica = $politica;
    }

    public function calcularTotal(DatosCobro $datos): int
    {
        return $this->politica->calcular($datos);
    }
}
