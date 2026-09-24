<?php
declare(strict_types=1);

/**
 * OBSERVER concreto — bitácora que ve el gerente: quién hizo qué, cuándo (RN12, atributo Seguridad).
 * De esos mismos eventos sale el reporte de trabajos por técnico (RF6).
 */
final class BitacoraDeAuditoria implements ObservadorDeOrden
{
    /** @var EventoOrden[] */
    private array $registros = [];

    public function alOcurrir(EventoOrden $evento): void
    {
        $this->registros[] = $evento;
    }

    /** @return string[] */
    public function lineas(): array
    {
        return array_map(fn (EventoOrden $e) => sprintf('%s | %-8s | %s | %s%s',
            $e->fecha->format('Y-m-d H:i'), $e->usuario, $e->orden->numeroRecibo,
            $e->tipo === EventoOrden::CAMBIO_ESTADO ? "{$e->estadoAnterior} → {$e->estadoNuevo}" : "[{$e->estadoAnterior}] {$e->tipo}",
            $e->nota !== '' ? "  ({$e->nota})" : ''), $this->registros);
    }

    /**
     * RF6 — órdenes que un técnico cerró (solucionadas o sin solución) en un rango de fechas.
     * @return string[] números de recibo
     */
    public function trabajosDe(string $tecnico, DateTimeImmutable $desde, DateTimeImmutable $hasta): array
    {
        $cerradas = array_filter($this->registros, fn (EventoOrden $e) =>
            $e->esCierre() && $e->usuario === $tecnico && $e->fecha >= $desde && $e->fecha <= $hasta);

        return array_values(array_map(fn (EventoOrden $e) => "{$e->orden->numeroRecibo} ({$e->estadoNuevo})", $cerradas));
    }
}
