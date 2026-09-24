<?php
declare(strict_types=1);

/**
 * STRATEGY — Contexto. Guarda qué regla corresponde a cada desenlace de la orden y delega el cálculo.
 *
 * La tabla desenlace → regla la arma el punto de composición con la política que fija el gerente.
 * Cambiar la política = cambiar una entrada de la tabla (usarRegla), sin tocar OrdenDeTrabajo
 * ni ninguna otra regla.
 */
final class CalculadoraDeCobro
{
    /** @param array<string, ReglaDeCobro> $reglas  desenlace de la orden → regla que se aplica */
    public function __construct(private array $reglas)
    {
    }

    public function usarRegla(string $desenlace, ReglaDeCobro $regla): void
    {
        $this->reglas[$desenlace] = $regla;
    }

    public function reglaPara(OrdenDeTrabajo $orden): ReglaDeCobro
    {
        $desenlace = $orden->desenlace();
        if ($desenlace === OrdenDeTrabajo::EN_CURSO) {
            throw new DomainException("Orden {$orden->numeroRecibo}: todavía no se dio de alta, no hay nada que cobrar.");
        }
        return $this->reglas[$desenlace]
            ?? throw new DomainException("No hay regla de cobro configurada para el desenlace {$desenlace}.");
    }

    public function cobroDe(OrdenDeTrabajo $orden): Cobro
    {
        return $this->reglaPara($orden)->calcular($orden);
    }
}
