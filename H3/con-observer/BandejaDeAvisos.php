<?php
declare(strict_types=1);

/**
 * OBSERVER concreto — la bandeja que ven las secretarias (RF5, RN9, RN10).
 * Solo le interesan los cierres (solucionada / sin solución) y las alertas de plazo:
 * en esos casos hay que LLAMAR al cliente. El resto de eventos los ignora.
 */
final class BandejaDeAvisos implements ObservadorDeOrden
{
    /** @var array<int, array{fecha: DateTimeImmutable, recibo: string, mensaje: string, leido: bool}> */
    private array $avisos = [];

    public function alOcurrir(EventoOrden $evento): void
    {
        $o = $evento->orden;
        $llamar = sprintf('Llamar a %s (%s)', $o->cliente->nombreCompleto, $o->cliente->telefonos());

        if ($evento->esCierre() && $evento->estadoNuevo === OrdenDeTrabajo::SOLUCIONADA) {
            $mensaje = sprintf('LISTO %s — %s: su %s está lista. Cobrar Bs %.2f. Garantía %d días.',
                $o->numeroRecibo, $llamar, $o->equipo->tipo(), $o->totalACobrar(), $o->garantiaDias());
        } elseif ($evento->esCierre()) {
            $mensaje = sprintf('SIN SOLUCIÓN %s — %s: puede recoger su %s (%s). Cobrar Bs %.2f.',
                $o->numeroRecibo, $llamar, $o->equipo->tipo(), $evento->nota, $o->totalACobrar());
        } elseif ($evento->esDePlazo()) {
            $etiqueta = $evento->tipo === EventoOrden::PLAZO_VENCIDO ? 'PLAZO VENCIDO' : 'PLAZO POR VENCER';
            $mensaje = sprintf('%s %s — %s. %s', $etiqueta, $o->numeroRecibo, $llamar, $evento->nota);
        } else {
            return;   // tomar, diagnosticar, entregar... no requieren que la secretaria llame
        }

        $this->avisos[] = ['fecha' => $evento->fecha, 'recibo' => $o->numeroRecibo, 'mensaje' => $mensaje, 'leido' => false];
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
