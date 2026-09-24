<?php
declare(strict_types=1);

/**
 * BUILDER — arma una OrdenDeTrabajo paso a paso, igual que la secretaria llena el informe técnico.
 *
 * La orden tiene 6 datos obligatorios (recibo, cliente, equipo, motivo, quién registra, foto)
 * y varios opcionales (descripción, observaciones, accesorios, pre-autorización).
 * El constructor de 11 parámetros posicionales era fácil de equivocar; el builder
 * nombra cada paso y, al final, informa TODOS los datos faltantes de una sola vez.
 */
final class OrdenDeTrabajoBuilder
{
    private ?Cliente $cliente = null;
    private ?Equipo $equipo = null;
    /** @var string[] */
    private array $motivos = [];
    private ?string $registradaPor = null;
    private ?DateTimeImmutable $fecha = null;
    private string $descripcion = '';
    /** @var string[] */
    private array $observaciones = [];
    /** @var string[] */
    private array $fotos = [];
    /** @var string[] */
    private array $accesorios = [];
    private bool $preAutorizada = false;

    private function __construct(private readonly string $numeroRecibo)
    {
    }

    public static function nuevaOrden(string $numeroRecibo): self
    {
        return new self($numeroRecibo);
    }

    public function paraCliente(Cliente $cliente): self
    {
        $this->cliente = $cliente;
        return $this;
    }

    public function conEquipo(Equipo $equipo): self
    {
        $this->equipo = $equipo;
        return $this;
    }

    /** Se puede llamar varias veces: un equipo puede venir por varios motivos (RN5). */
    public function porMotivo(string $motivo): self
    {
        $this->motivos[] = $motivo;
        return $this;
    }

    public function contadoPorElCliente(string $descripcion): self
    {
        $this->descripcion = $descripcion;
        return $this;
    }

    /** Pernos faltantes, golpes, daños (RN4). */
    public function conObservacion(string $observacion): self
    {
        $this->observaciones[] = $observacion;
        return $this;
    }

    public function conFoto(string $ruta): self
    {
        $this->fotos[] = $ruta;
        return $this;
    }

    public function dejaAccesorio(string $accesorio): self
    {
        $this->accesorios[] = $accesorio;
        return $this;
    }

    /** El cliente pidió el servicio directamente (mantenimiento, SO, SSD, RAM): no hace falta llamarle (RN6). */
    public function preAutorizada(): self
    {
        $this->preAutorizada = true;
        return $this;
    }

    public function registradaPor(string $usuario, DateTimeImmutable $fecha): self
    {
        $this->registradaPor = $usuario;
        $this->fecha = $fecha;
        return $this;
    }

    public function build(): OrdenDeTrabajo
    {
        $faltan = [];
        if ($this->cliente === null)       { $faltan[] = 'cliente'; }
        if ($this->equipo === null)        { $faltan[] = 'equipo'; }
        if ($this->motivos === [])         { $faltan[] = 'al menos un motivo'; }
        if ($this->fotos === [])           { $faltan[] = 'al menos una foto'; }
        if ($this->registradaPor === null) { $faltan[] = 'usuario que registra'; }
        if ($faltan !== []) {
            throw new InvalidArgumentException("Orden {$this->numeroRecibo} incompleta. Falta: " . implode(', ', $faltan) . '.');
        }

        // La orden sigue validando sus propias reglas (motivos válidos, pre-autorización, etc.).
        return new OrdenDeTrabajo(
            numeroRecibo: $this->numeroRecibo,
            cliente: $this->cliente,
            equipo: $this->equipo,
            motivos: array_values(array_unique($this->motivos)),
            registradaPor: $this->registradaPor,
            fechaIngreso: $this->fecha,
            descripcionCliente: $this->descripcion,
            observacionesFisicas: $this->observaciones,
            fotos: $this->fotos,
            accesoriosDejados: $this->accesorios,
            preAutorizada: $this->preAutorizada,
        );
    }
}
