<?php
declare(strict_types=1);

class Cliente
{
    public function __construct(
        public int $id,
        public string $nombreCompleto,
        public string $celularPrincipal,
        public ?string $celularAlterno = null
    ) {
        if ($id <= 0 || trim($nombreCompleto) === '' || trim($celularPrincipal) === '') {
            throw new InvalidArgumentException('El cliente necesita ID positivo, nombre y celular.');
        }
    }
}
