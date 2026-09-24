<?php
declare(strict_types=1);

/**
 * FUSIÓN — aquí se encuentran los dos patrones.
 *
 * Es un OBSERVADOR de las órdenes (decide CUÁNDO y A QUIÉN avisar: a las secretarias,
 * cuando una orden se cierra o se acerca al plazo de 6 meses) y usa la STRATEGY de cobro
 * (CalculadoraDeCobro) para decir CUÁNTO cobrar en el mismo aviso.
 * Así la secretaria llama al cliente con el monto correcto, calculado con la misma regla
 * que después usa el mostrador al entregar.
 */
final class BandejaDeAvisos implements ObservadorDeOrden
{
    /** @var array<int, array{fecha: DateTimeImmutable, recibo: string, mensaje: string, leido: bool}> */
    private array $avisos = [];

    public function __construct(private readonly CalculadoraDeCobro $calculadora)
    {
    }

    public function alOcurrir(EventoOrden $evento): void
    {
        if (!$evento->esCierre() && !$evento->esDePlazo()) {
            return;   // tomar, diagnosticar, entregar... no requieren llamar al cliente
        }

        $o = $evento->orden;
        $llamar  = sprintf('Llamar a %s (%s)', $o->cliente->nombreCompleto, $o->cliente->telefonos());
        $aCobrar = $this->montoACobrar($o);

        if ($evento->esDePlazo()) {
            $etiqueta = $evento->tipo === EventoOrden::PLAZO_VENCIDO ? 'PLAZO VENCIDO' : 'PLAZO POR VENCER';
            $mensaje = sprintf('%s %s — %s. %s %s', $etiqueta, $o->numeroRecibo, $llamar, $evento->nota, $aCobrar);
        } elseif ($evento->estadoNuevo === OrdenDeTrabajo::SOLUCIONADA) {
            $mensaje = sprintf('LISTO %s — %s: su %s está lista. %s', $o->numeroRecibo, $llamar, $o->equipo->tipo(), $aCobrar);
        } else {
            $mensaje = sprintf('SIN SOLUCIÓN %s — %s: puede recoger su %s (%s). %s',
                $o->numeroRecibo, $llamar, $o->equipo->tipo(), $evento->nota, $aCobrar);
        }

        $this->avisos[] = ['fecha' => $evento->fecha, 'recibo' => $o->numeroRecibo, 'mensaje' => $mensaje, 'leido' => false];
    }

    /** STRATEGY dentro del OBSERVER. Si el cálculo falla, el aviso igual se genera: avisar es lo crítico. */
    private function montoACobrar(OrdenDeTrabajo $orden): string
    {
        if ($orden->desenlace() === OrdenDeTrabajo::EN_CURSO) {
            return 'Todavía está en trabajo.';
        }
        try {
            $cobro = $this->calculadora->cobroDe($orden);
            return $cobro->total() > 0
                ? sprintf('Cobrar Bs %.2f (%s).', $cobro->total(), $cobro->detalle())
                : sprintf('No se cobra: %s.', $cobro->detalle());
        } catch (DomainException $e) {
            return 'Monto por confirmar con el gerente.';
        }
    }

    /** @return array<int, array{fecha: DateTimeImmutable, recibo: string, mensaje: string, leido: bool}> */
    public function pendientes(): array
    {
        return array_filter($this->avisos, fn (array $a) => !$a['leido']);
    }

    public function marcarLeido(int $indice): void
    {
        $this->avisos[$indice]['leido'] = true;
    }
}
