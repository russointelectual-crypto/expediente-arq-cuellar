<?php
declare(strict_types=1);

/** STRATEGY concreta — se reparó: mano de obra + cada repuesto sacado del inventario (RN8). */
final class CobroPorReparacion implements ReglaDeCobro
{
    public function nombre(): string
    {
        return 'Reparación';
    }

    public function calcular(OrdenDeTrabajo $orden): Cobro
    {
        $lineas = [['concepto' => 'Mano de obra', 'monto' => $orden->manoDeObra()]];
        foreach ($orden->repuestos() as $repuesto) {
            $lineas[] = ['concepto' => $repuesto['descripcion'], 'monto' => (float) $repuesto['precio']];
        }
        return new Cobro($this->nombre(), $lineas, "garantía {$orden->garantiaDias()} días");
    }
}
