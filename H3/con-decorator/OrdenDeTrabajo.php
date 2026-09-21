<?php
declare(strict_types=1);

require_once __DIR__ . '/Cliente.php';
require_once __DIR__ . '/Equipo.php';

class OrdenDeTrabajo
{
    private string $estado = 'recibida';

    public function __construct(
        public string $numeroRecibo,
        public Cliente $cliente,
        public Equipo $equipo,
        public string $motivoIngreso,
        public DateTimeImmutable $fechaIngreso,
        public ?DateTimeImmutable $fechaCompromiso = null
    ) {
        if (trim($numeroRecibo) === '' || trim($motivoIngreso) === '') {
            throw new InvalidArgumentException('El recibo y el motivo son obligatorios.');
        }
        if ($fechaCompromiso !== null && $fechaCompromiso < $fechaIngreso) {
            throw new InvalidArgumentException('El compromiso no puede ser anterior al ingreso.');
        }
    }

    public function estadoActual(): string
    {
        return $this->estado;
    }

    // Subconjunto del H2 para este laboratorio. No implementa el patron State.
    public function aplicar(string $evento): void
    {
        $transiciones = [
            'recibida' => ['DIAGNOSTICO_LISTO' => 'diagnosticada'],
            'diagnosticada' => [
                'CLIENTE_AUTORIZA' => 'en reparacion',
                'CLIENTE_RECHAZA' => 'devuelta sin solucion'
            ],
            'en reparacion' => [
                'REPARACION_TERMINADA' => 'lista',
                'SIN_SOLUCION' => 'devuelta sin solucion'
            ],
            'lista' => ['EQUIPO_ENTREGADO' => 'entregada']
        ];
        if (!isset($transiciones[$this->estado][$evento])) {
            throw new DomainException('Evento ' . $evento . ' no permitido desde ' . $this->estado . '.');
        }
        $this->estado = $transiciones[$this->estado][$evento];
    }

    public function diasEnTaller(DateTimeImmutable $hoy): int
    {
        if ($hoy < $this->fechaIngreso) {
            throw new InvalidArgumentException('La consulta no puede ser anterior al ingreso.');
        }
        return (int) $this->fechaIngreso->diff($hoy)->format('%a');
    }
}
