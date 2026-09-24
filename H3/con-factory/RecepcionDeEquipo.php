<?php
declare(strict_types=1);

/**
 * FACTORY METHOD — Creador abstracto.
 *
 * Recibir un equipo es siempre el mismo trámite (crear el equipo, armar la orden,
 * dejar las notas del recibo). Lo único que cambia según el tipo es QUÉ equipo
 * concreto se crea: esa decisión se delega a las subclases mediante crearEquipo().
 */
abstract class RecepcionDeEquipo
{
    /** El Factory Method: cada subclase decide qué clase concreta de Equipo instanciar. */
    abstract protected function crearEquipo(array $datos): Equipo;

    /** Nombre del tipo que atiende este creador (para el selector del formulario). */
    abstract public function tipoQueRecibe(): string;

    /**
     * Operación común (no cambia con el tipo): usa el Factory Method y arma la orden.
     *
     * @param array $formulario  lo que llega del formulario de recepción ($_POST)
     */
    public function recibir(string $numeroRecibo, Cliente $cliente, array $formulario, string $usuario, DateTimeImmutable $fecha): OrdenDeTrabajo
    {
        $equipo = $this->crearEquipo($formulario);

        return new OrdenDeTrabajo(
            $numeroRecibo,
            $cliente,
            $equipo,
            $formulario['motivos'] ?? [],
            $usuario,
            $fecha,
            $formulario['descripcion'] ?? '',
            $formulario['observaciones'] ?? [],
            $formulario['fotos'] ?? [],
            $formulario['accesorios'] ?? [],
            $formulario['preAutorizada'] ?? false,
        );
    }

    /** Ayuda común a las subclases: lee un campo obligatorio del formulario. */
    protected function requerido(array $datos, string $campo): mixed
    {
        if (!array_key_exists($campo, $datos) || $datos[$campo] === '' || $datos[$campo] === null) {
            throw new InvalidArgumentException(sprintf('%s: falta el campo "%s".', $this->tipoQueRecibe(), $campo));
        }
        return $datos[$campo];
    }
}
