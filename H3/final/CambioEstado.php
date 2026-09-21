<?php
declare(strict_types=1);

final class CambioEstado
{
    public function __construct(
        public string $numeroRecibo,
        public string $cliente,
        public string $celular,
        public string $estadoAnterior,
        public string $estadoActual
    ) {
    }
}
