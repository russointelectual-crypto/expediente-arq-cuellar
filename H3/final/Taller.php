<?php
declare(strict_types=1);

/**
 * Punto de composición del módulo: el ÚNICO lugar que hace `new` de las piezas y las conecta.
 * (Esto reemplaza al Singleton: se crea una vez por petición y se pasa a quien lo necesita.)
 */
final class Taller
{
    public readonly CalculadoraDeCobro $calculadora;
    public readonly BandejaDeAvisos $bandeja;
    public readonly BitacoraDeAuditoria $bitacora;
    public readonly Mostrador $mostrador;

    public function __construct()
    {
        // STRATEGY — política de cobro vigente (la define el gerente).
        $this->calculadora = new CalculadoraDeCobro([
            OrdenDeTrabajo::SOLUCIONADA            => new CobroPorReparacion(),
            OrdenDeTrabajo::CLIENTE_NO_AUTORIZO    => new CobroPorDiagnostico(),
            OrdenDeTrabajo::SIN_REPARACION_POSIBLE => new SinCargo(),
        ]);

        // OBSERVER — interesados en lo que pasa con las órdenes.
        $this->bandeja  = new BandejaDeAvisos($this->calculadora);
        $this->bitacora = new BitacoraDeAuditoria();

        $this->mostrador = new Mostrador($this->calculadora);
    }

    /** Toda orden que se registra (o que se carga de la base de datos) pasa por aquí. */
    public function abrir(OrdenDeTrabajo $orden): OrdenDeTrabajo
    {
        $orden->suscribir($this->bandeja);
        $orden->suscribir($this->bitacora);
        return $orden;
    }

    /** Lo que ejecuta la tarea programada cada mañana (RN10). */
    public function revisarPlazos(iterable $ordenes, DateTimeImmutable $hoy): void
    {
        foreach ($ordenes as $orden) {
            $orden->revisarPlazo($hoy);
        }
    }
}
