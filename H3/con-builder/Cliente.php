<?php
declare(strict_types=1);

/**
 * Cliente del taller (RN2).
 * Se registra con nombre completo y hasta dos celulares; el carnet es opcional al dejar
 * el equipo y se exige al retirar si el cliente perdió su recibo (RN11).
 */
final class Cliente
{
    public function __construct(
        public readonly string $nombreCompleto,
        public readonly string $celular1,
        public readonly ?string $celular2 = null,
        public readonly ?string $carnet = null,
    ) {
        $partes = preg_split('/\s+/', trim($nombreCompleto));
        if (count($partes) < 2) {
            throw new InvalidArgumentException('El nombre completo debe tener al menos nombre y apellido.');
        }
        self::validarCelular($celular1);
        if ($celular2 !== null) {
            self::validarCelular($celular2);
        }
    }

    /** Celulares en Bolivia: 8 dígitos que empiezan con 6 o 7. */
    private static function validarCelular(string $numero): void
    {
        if (!preg_match('/^[67]\d{7}$/', $numero)) {
            throw new InvalidArgumentException("Celular inválido: {$numero} (se esperan 8 dígitos que empiecen con 6 o 7).");
        }
    }

    /** RF2: buscar por parte del nombre o por cualquiera de los dos celulares. */
    public function coincideCon(string $texto): bool
    {
        $texto = trim($texto);
        if ($texto === '') {
            return false;
        }
        return $texto === $this->celular1
            || $texto === $this->celular2
            || stripos($this->nombreCompleto, $texto) !== false;
    }

    public function telefonos(): string
    {
        return $this->celular2 !== null ? "{$this->celular1} / {$this->celular2}" : $this->celular1;
    }
}
