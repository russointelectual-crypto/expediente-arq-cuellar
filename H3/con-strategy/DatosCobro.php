<?php
declare(strict_types=1);

class DatosCobro
{
    // Importes en centavos de boliviano para evitar calculos con decimales.
    public function __construct(
        public int $diagnostico,
        public int $manoDeObra,
        public int $repuestos
    ) {
        if ($diagnostico < 0 || $manoDeObra < 0 || $repuestos < 0) {
            throw new InvalidArgumentException('Los importes no pueden ser negativos.');
        }
    }
}
