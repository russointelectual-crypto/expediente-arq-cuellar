<?php
declare(strict_types=1);

/**
 * STRATEGY concreta — se cobra solo el diagnóstico, y SOLO si la falla es electrónica (RN7).
 * La condición "solo electrónica" vive aquí, junto a la regla que la usa, no dispersa en la orden.
 */
final class CobroPorDiagnostico implements ReglaDeCobro
{
    public function nombre(): string
    {
        return 'Solo diagnóstico';
    }

    public function calcular(OrdenDeTrabajo $orden): Cobro
    {
        if ($orden->tipoTrabajo() !== OrdenDeTrabajo::TRABAJO_ELECTRONICA) {
            return new Cobro($this->nombre(), [], 'la falla no es electrónica (RN7)');
        }
        return new Cobro($this->nombre(), [['concepto' => 'Diagnóstico electrónico', 'monto' => $orden->costoDiagnostico()]]);
    }
}
