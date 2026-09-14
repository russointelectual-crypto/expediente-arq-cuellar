<?php
declare(strict_types=1);

class Aviso
{
    public function __construct(
        public string $tipo,
        public string $destinatario,
        public string $mensaje,
        public DateTimeImmutable $fecha
    ) {
        if (trim($tipo) === '' || trim($destinatario) === '' || trim($mensaje) === '') {
            throw new InvalidArgumentException('Tipo, destinatario y mensaje son obligatorios.');
        }
    }
}
