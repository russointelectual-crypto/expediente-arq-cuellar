<?php
declare(strict_types=1);

/**
 * ADAPTER — Adaptador: hace que la librería del banco (SdkQrBanco) se vea como un MetodoDePago más.
 *
 * Traduce en los dos sentidos:
 *   taller → banco : Bs con decimales → centavos + 'BOB'; concepto largo → glosa de 25 caracteres.
 *   banco → taller : arreglo + código numérico → ComprobanteDePago o una excepción con mensaje claro.
 *
 * Si mañana el taller cambia de banco, se escribe OTRO adaptador; Caja y OrdenDeTrabajo no se tocan.
 */
final class AdaptadorPagoQr implements MetodoDePago
{
    public function __construct(
        private readonly SdkQrBanco $banco,
        private readonly int $consultasMaximas = 5,
    ) {
    }

    public function nombre(): string
    {
        return 'QR';
    }

    public function cobrar(float $montoBs, string $concepto): ComprobanteDePago
    {
        $qr = $this->banco->generarQR(
            (int) round($montoBs * 100),                    // Bs → centavos
            'BOB',
            substr(self::sinTildes($concepto), 0, 25),      // la glosa del banco es corta y sin tildes
        );
        // En la app real: se muestra $qr['qr_base64'] en la pantalla de caja y se consulta cada pocos segundos.

        for ($i = 1; $i <= $this->consultasMaximas; $i++) {
            $estado = $this->banco->consultarEstado($qr['id_transaccion']);
            if ($estado === SdkQrBanco::ESTADO_PAGADO) {
                return new ComprobanteDePago($this->nombre(), $montoBs, $concepto, $qr['id_transaccion']);
            }
            if ($estado === SdkQrBanco::ESTADO_EXPIRADO) {
                throw new RuntimeException("El QR {$qr['id_transaccion']} expiró sin pago: genere uno nuevo o cobre en efectivo.");
            }
        }
        throw new RuntimeException("El banco todavía no confirma el QR {$qr['id_transaccion']}: no entregue el equipo aún.");
    }

    private static function sinTildes(string $texto): string
    {
        return strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
                              'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ñ' => 'N', '°' => '']);
    }
}
