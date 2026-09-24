<?php
declare(strict_types=1);

/** DECORATOR — Componente: cualquier cosa que el taller cobra y describe en el recibo. */
interface ServicioCobrable
{
    public function descripcion(): string;

    public function precio(): float;

    /** @return array<int, array{concepto: string, monto: float, esRepuesto: bool}> */
    public function lineas(): array;
}
