<?php
declare(strict_types=1);

/**
 * ADAPTER — Objetivo (Target): así quiere cobrar el sistema del taller, en su propio idioma
 * (bolivianos con decimales, un comprobante como objeto). Viene del contrato MetodoDePago del H2.
 */
interface MetodoDePago
{
    public function nombre(): string;

    public function cobrar(float $montoBs, string $concepto): ComprobanteDePago;
}
