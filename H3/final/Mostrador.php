<?php
declare(strict_types=1);

/**
 * Mostrador (módulo Caja): entrega el equipo y cobra.
 * Usa la MISMA CalculadoraDeCobro (STRATEGY) que la bandeja: lo que la secretaria dijo por
 * teléfono es exactamente lo que se cobra. La entrega dispara el evento ENTREGADA (OBSERVER),
 * que queda en la bitácora con el usuario que cobró.
 */
final class Mostrador
{
    public const EFECTIVO = 'EFECTIVO';
    public const QR       = 'QR';

    /** @var array<int, array{recibo: string, usuario: string, metodo: string, monto: float, detalle: string}> */
    private array $movimientos = [];

    public function __construct(private readonly CalculadoraDeCobro $calculadora)
    {
    }

    public function entregar(
        OrdenDeTrabajo $orden,
        string $usuario,
        string $metodoDePago,
        string $verificacion,
        ?string $carnet = null,
        ?DateTimeImmutable $fecha = null,
    ): Cobro {
        if (!in_array($metodoDePago, [self::EFECTIVO, self::QR], true)) {
            throw new InvalidArgumentException('El taller cobra en EFECTIVO o por QR.');
        }
        $cobro = $this->calculadora->cobroDe($orden);                   // STRATEGY
        $orden->entregar($usuario, $verificacion, $carnet, $fecha);     // valida y publica ENTREGADA (OBSERVER)

        if ($cobro->total() > 0) {
            $this->movimientos[] = [
                'recibo'  => $orden->numeroRecibo,
                'usuario' => $usuario,
                'metodo'  => $metodoDePago,
                'monto'   => $cobro->total(),
                'detalle' => $cobro->detalle(),
            ];
        }
        return $cobro;
    }

    /**
     * RF6 — cierre de caja: el EFECTIVO es lo que tiene que haber en el cajón; el QR ya está en el banco.
     * @return array{EFECTIVO: float, QR: float, total: float, movimientos: array}
     */
    public function cierreDeCaja(): array
    {
        $efectivo = (float) array_sum(array_column(array_filter($this->movimientos, fn ($m) => $m['metodo'] === self::EFECTIVO), 'monto'));
        $qr       = (float) array_sum(array_column(array_filter($this->movimientos, fn ($m) => $m['metodo'] === self::QR), 'monto'));

        return ['EFECTIVO' => $efectivo, 'QR' => $qr, 'total' => $efectivo + $qr, 'movimientos' => $this->movimientos];
    }
}
