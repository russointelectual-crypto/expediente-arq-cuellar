<?php
declare(strict_types=1);

require_once __DIR__ . '/IPoliticaCobro.php';

final class ServicioCaja
{
    public function __construct(private IPoliticaCobro $politica)
    {
    }

    public function cambiarPolitica(IPoliticaCobro $politica): void
    {
        $this->politica = $politica;
    }

    public function metodoActual(): string
    {
        return $this->politica->nombre();
    }

    public function preparar(DatosCobro $datos): string
    {
        return $this->politica->preparar($datos);
    }

    public function cobrar(DatosCobro $datos): string
    {
        return $this->politica->cobrar($datos);
    }
}
