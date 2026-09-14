<?php
declare(strict_types=1);

require_once __DIR__ . '/cargar.php';

class OrdenDeTrabajoBuilder
{
    private ?string $recibo = null;
    private ?Cliente $cliente = null;
    private ?Equipo $equipo = null;
    private ?string $motivo = null;
    private ?DateTimeImmutable $ingreso = null;
    private ?DateTimeImmutable $compromiso = null;

    public function conRecibo(string $recibo): self
    {
        $this->recibo = $recibo;
        return $this;
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

    public function porMotivo(string $motivo): self
    {
        $this->motivo = $motivo;
        return $this;
    }

    public function ingresadaEl(DateTimeImmutable $fecha): self
    {
        $this->ingreso = $fecha;
        return $this;
    }

    public function comprometidaPara(?DateTimeImmutable $fecha): self
    {
        $this->compromiso = $fecha;
        return $this;
    }

    public function construir(): OrdenDeTrabajo
    {
        if ($this->recibo === null || $this->cliente === null || $this->equipo === null
            || $this->motivo === null || $this->ingreso === null) {
            throw new LogicException('Faltan datos obligatorios para construir la orden.');
        }
        $orden = new OrdenDeTrabajo($this->recibo, $this->cliente, $this->equipo,
            $this->motivo, $this->ingreso, $this->compromiso);
        // Cada construccion correcta reinicia el builder para no arrastrar datos.
        $this->recibo = null;
        $this->cliente = null;
        $this->equipo = null;
        $this->motivo = null;
        $this->ingreso = null;
        $this->compromiso = null;
        return $orden;
    }
}
