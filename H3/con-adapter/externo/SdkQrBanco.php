<?php
declare(strict_types=1);

/**
 * ADAPTADO (Adaptee) — SIMULA la librería que entrega el banco para cobrar con QR.
 *
 * ESTE ARCHIVO NO SE MODIFICA: representa código de terceros. Su "idioma" no es el del taller:
 *   - trabaja en CENTAVOS (int) y exige la moneda como texto ('BOB');
 *   - responde arreglos con claves en snake_case, no objetos;
 *   - informa el estado con códigos numéricos (0 pendiente, 1 pagado, 2 expirado);
 *   - la glosa admite como máximo 25 caracteres.
 *
 * La simulación: cada QR figura PENDIENTE en la primera consulta y PAGADO en la segunda
 * (como si el cliente tardara unos segundos en pagar desde su app).
 */
final class SdkQrBanco
{
    public const ESTADO_PENDIENTE = 0;
    public const ESTADO_PAGADO    = 1;
    public const ESTADO_EXPIRADO  = 2;

    /** @var array<string, int> consultas hechas por transacción (solo para la simulación) */
    private array $consultas = [];

    public function __construct(
        private readonly string $apiKey,
        private readonly string $cuentaDestino,
        private readonly bool $simularQueNadiePaga = false,
    ) {
    }

    /** @return array{id_transaccion: string, qr_base64: string, monto_centavos: int, expira_en: string} */
    public function generarQR(int $montoCentavos, string $moneda, string $glosa, int $vigenciaMinutos = 10): array
    {
        if ($moneda !== 'BOB') {
            throw new RuntimeException('BANCO-ERR-01: moneda no soportada');
        }
        if ($montoCentavos <= 0) {
            throw new RuntimeException('BANCO-ERR-02: monto inválido');
        }
        if (strlen($glosa) > 25) {
            throw new RuntimeException('BANCO-ERR-03: glosa excede 25 caracteres');
        }
        $id = 'TX' . strtoupper(substr(hash('sha256', $this->cuentaDestino . $glosa . $montoCentavos . microtime()), 0, 10));
        $this->consultas[$id] = 0;

        return [
            'id_transaccion' => $id,
            'qr_base64'      => base64_encode("QR|{$this->cuentaDestino}|{$montoCentavos}|{$glosa}"),
            'monto_centavos' => $montoCentavos,
            'expira_en'      => (new DateTimeImmutable("+{$vigenciaMinutos} minutes"))->format(DATE_ATOM),
        ];
    }

    public function consultarEstado(string $idTransaccion): int
    {
        if (!array_key_exists($idTransaccion, $this->consultas)) {
            throw new RuntimeException('BANCO-ERR-04: transacción inexistente');
        }
        $this->consultas[$idTransaccion]++;
        if ($this->simularQueNadiePaga) {
            return $this->consultas[$idTransaccion] >= 3 ? self::ESTADO_EXPIRADO : self::ESTADO_PENDIENTE;
        }
        return $this->consultas[$idTransaccion] >= 2 ? self::ESTADO_PAGADO : self::ESTADO_PENDIENTE;
    }
}
