<?php
declare(strict_types=1);

/**
 * Orden de trabajo: un equipo, desde que entra hasta que sale (RF1, RF3).
 * Un número de recibo por equipo (RN1).
 *
 * FUSIÓN (h3/final) — en esta clase conviven los dos patrones:
 *   OBSERVER : la orden es el SUJETO; publica un EventoOrden en cada cambio de estado
 *              y en la revisión diaria del plazo de 6 meses.
 *   STRATEGY : la orden NO calcula el cobro; solo expone su desenlace() para que la
 *              CalculadoraDeCobro elija la ReglaDeCobro que corresponde.
 */
final class OrdenDeTrabajo
{
    // ---- Estados (RF3) ----
    public const RECIBIDA               = 'RECIBIDA';
    public const EN_DIAGNOSTICO         = 'EN_DIAGNOSTICO';
    public const ESPERANDO_AUTORIZACION = 'ESPERANDO_AUTORIZACION';
    public const EN_REPARACION          = 'EN_REPARACION';
    public const SOLUCIONADA            = 'SOLUCIONADA';
    public const SIN_SOLUCION           = 'SIN_SOLUCION';
    public const ENTREGADA              = 'ENTREGADA';

    /** Tabla de transiciones permitidas: reemplaza el switch del H1 (OCP). */
    private const TRANSICIONES = [
        self::RECIBIDA               => [self::EN_DIAGNOSTICO],
        self::EN_DIAGNOSTICO         => [self::ESPERANDO_AUTORIZACION, self::EN_REPARACION, self::SIN_SOLUCION],
        self::ESPERANDO_AUTORIZACION => [self::EN_REPARACION, self::SIN_SOLUCION],
        self::EN_REPARACION          => [self::SOLUCIONADA, self::SIN_SOLUCION],
        self::SOLUCIONADA            => [self::ENTREGADA],
        self::SIN_SOLUCION           => [self::ENTREGADA],
        self::ENTREGADA              => [],
    ];

    // ---- Motivos de ingreso (RN5) ----
    public const MOTIVOS = [
        'NO_ENCIENDE', 'INGRESO_LIQUIDO', 'BISAGRA_ROTA', 'PANTALLA_DANADA', 'TECLADO', 'BATERIA',
        'FALLA_SOFTWARE', 'ACTUALIZAR_SO', 'UPGRADE_SSD', 'UPGRADE_RAM', 'MANTENIMIENTO', 'OTRO',
    ];
    /** Servicios que el cliente pide directamente: pueden entrar pre-autorizados (RN6). */
    public const SERVICIOS_DIRECTOS = ['ACTUALIZAR_SO', 'UPGRADE_SSD', 'UPGRADE_RAM', 'MANTENIMIENTO'];

    // ---- Tipo de trabajo que determina el técnico al diagnosticar ----
    public const TRABAJO_ELECTRONICA   = 'ELECTRONICA';
    public const TRABAJO_SOFTWARE      = 'SOFTWARE';
    public const TRABAJO_MANTENIMIENTO = 'MANTENIMIENTO';
    public const TRABAJO_ACTUALIZACION = 'ACTUALIZACION';
    private const TIPOS_DE_TRABAJO = [
        self::TRABAJO_ELECTRONICA, self::TRABAJO_SOFTWARE, self::TRABAJO_MANTENIMIENTO, self::TRABAJO_ACTUALIZACION,
    ];

    // ---- Por qué una orden termina sin solución ----
    public const CLIENTE_NO_AUTORIZO    = 'CLIENTE_NO_AUTORIZO';
    public const SIN_REPARACION_POSIBLE = 'SIN_REPARACION_POSIBLE';

    // ---- Verificación al entregar (RN11) ----
    public const CON_RECIBO = 'RECIBO';
    public const CON_CARNET = 'CARNET';

    // ---- Plazo máximo en el taller (RN10) ----
    public const PLAZO_MAXIMO      = '+6 months';
    public const AVISAR_DIAS_ANTES = 30;

    /** @var ObservadorDeOrden[] */
    private array $observadores = [];

    private string $estado = self::RECIBIDA;
    private ?string $tecnico = null;
    private ?string $diagnostico = null;
    private ?string $tipoTrabajo = null;
    private float $presupuesto = 0.0;
    private float $costoDiagnostico = 0.0;
    /** @var string[] */
    private array $trabajosRealizados = [];
    /** @var array<int, array{descripcion: string, precio: float}> */
    private array $repuestos = [];
    private float $manoDeObra = 0.0;
    private int $garantiaDias = 0;
    private ?string $motivoSinSolucion = null;
    private ?DateTimeImmutable $fechaAlta = null;
    private ?DateTimeImmutable $fechaEntrega = null;
    private ?string $entregadaPor = null;
    private ?string $verificacionEntrega = null;

    /**
     * @param string[] $motivos
     * @param string[] $observacionesFisicas  pernos faltantes, golpes, daños (RN4)
     * @param string[] $fotos                 rutas de las fotografías (RN4)
     * @param string[] $accesoriosDejados     cargador, mouse, maletín...
     */
    public function __construct(
        public readonly string $numeroRecibo,
        public readonly Cliente $cliente,
        public readonly Equipo $equipo,
        public readonly array $motivos,
        public readonly string $registradaPor,
        public readonly DateTimeImmutable $fechaIngreso,
        public readonly string $descripcionCliente = '',
        public readonly array $observacionesFisicas = [],
        public readonly array $fotos = [],
        public readonly array $accesoriosDejados = [],
        public readonly bool $preAutorizada = false,
    ) {
        if (trim($numeroRecibo) === '' || trim($registradaPor) === '') {
            throw new InvalidArgumentException('Número de recibo y usuario que registra son obligatorios.');
        }
        if ($motivos === []) {
            throw new InvalidArgumentException('Debe indicar al menos un motivo de ingreso.');
        }
        foreach ($motivos as $motivo) {
            if (!in_array($motivo, self::MOTIVOS, true)) {
                throw new InvalidArgumentException("Motivo de ingreso desconocido: {$motivo}.");
            }
        }
        if ($fotos === []) {
            throw new InvalidArgumentException('Se requiere al menos una fotografía del equipo al ingresar.');
        }
        if ($preAutorizada && array_diff($motivos, self::SERVICIOS_DIRECTOS) !== []) {
            throw new InvalidArgumentException('Solo los servicios pedidos directamente (mantenimiento, SO, SSD, RAM) pueden entrar pre-autorizados.');
        }
    }

    // =====================================================================
    //  OBSERVER — suscripción
    // =====================================================================

    public function suscribir(ObservadorDeOrden $observador): void
    {
        if (!in_array($observador, $this->observadores, true)) {
            $this->observadores[] = $observador;
        }
    }

    public function desuscribir(ObservadorDeOrden $observador): void
    {
        $this->observadores = array_values(array_filter($this->observadores, fn ($o) => $o !== $observador));
    }

    // =====================================================================
    //  Transiciones del flujo (RF3)
    // =====================================================================

    /** El técnico toma la orden por número de recibo (cola por orden de llegada, RN6). */
    public function tomar(string $tecnico, ?DateTimeImmutable $fecha = null): void
    {
        $this->exigirTransicion(self::EN_DIAGNOSTICO);
        $this->tecnico = $tecnico;
        $this->pasarA(self::EN_DIAGNOSTICO, $tecnico, $fecha);
    }

    /** Diagnóstico: si el servicio vino pre-autorizado pasa directo a reparación; si no, espera al cliente. */
    public function registrarDiagnostico(
        string $tecnico,
        string $detalle,
        string $tipoTrabajo,
        float $presupuesto,
        float $costoDiagnostico = 0.0,
        ?DateTimeImmutable $fecha = null,
    ): void {
        $this->exigirTecnicoAsignado($tecnico);
        if (trim($detalle) === '') {
            throw new InvalidArgumentException('El diagnóstico no puede estar vacío.');
        }
        if (!in_array($tipoTrabajo, self::TIPOS_DE_TRABAJO, true)) {
            throw new InvalidArgumentException("Tipo de trabajo desconocido: {$tipoTrabajo}.");
        }
        if ($presupuesto < 0 || $costoDiagnostico < 0) {
            throw new InvalidArgumentException('Presupuesto y costo de diagnóstico no pueden ser negativos.');
        }

        $siguiente = $this->preAutorizada ? self::EN_REPARACION : self::ESPERANDO_AUTORIZACION;
        $this->exigirTransicion($siguiente);
        $this->diagnostico = $detalle;
        $this->tipoTrabajo = $tipoTrabajo;
        $this->presupuesto = $presupuesto;
        $this->costoDiagnostico = $costoDiagnostico;
        $this->pasarA($siguiente, $tecnico, $fecha, $detalle);
    }

    /** El técnico llamó al cliente (RN6). Si no autoriza, la orden termina sin solución (RN7). */
    public function registrarRespuestaCliente(string $tecnico, bool $autoriza, ?DateTimeImmutable $fecha = null): void
    {
        $this->exigirTecnicoAsignado($tecnico);
        if ($autoriza) {
            $this->pasarA(self::EN_REPARACION, $tecnico, $fecha, 'El cliente autorizó el trabajo');
            return;
        }
        $this->exigirTransicion(self::SIN_SOLUCION);
        $this->motivoSinSolucion = self::CLIENTE_NO_AUTORIZO;
        $this->fechaAlta = $fecha ?? new DateTimeImmutable();
        $this->pasarA(self::SIN_SOLUCION, $tecnico, $this->fechaAlta, 'El cliente no autorizó el trabajo');
    }

    /**
     * Dar de alta con solución (RN8).
     * @param string[] $trabajos
     * @param array<int, array{descripcion: string, precio: float}> $repuestos  sacados del inventario
     */
    public function registrarSolucion(
        string $tecnico,
        array $trabajos,
        array $repuestos,
        float $manoDeObra,
        int $garantiaDias,
        ?DateTimeImmutable $fecha = null,
    ): void {
        $this->exigirTecnicoAsignado($tecnico);
        if ($trabajos === []) {
            throw new InvalidArgumentException('Debe detallar al menos un trabajo realizado.');
        }
        foreach ($repuestos as $r) {
            if (!isset($r['descripcion'], $r['precio']) || $r['precio'] < 0) {
                throw new InvalidArgumentException('Cada repuesto necesita descripción y precio no negativo.');
            }
        }
        if ($manoDeObra < 0 || $garantiaDias < 0) {
            throw new InvalidArgumentException('Mano de obra y garantía no pueden ser negativas.');
        }

        $this->exigirTransicion(self::SOLUCIONADA);
        $this->trabajosRealizados = $trabajos;
        $this->repuestos = $repuestos;
        $this->manoDeObra = $manoDeObra;
        $this->garantiaDias = $garantiaDias;
        $this->fechaAlta = $fecha ?? new DateTimeImmutable();
        $this->pasarA(self::SOLUCIONADA, $tecnico, $this->fechaAlta, implode(', ', $trabajos));
    }

    /** El taller no logró solucionar: igual se registra lo que se hizo y el equipo se devuelve (RN8). */
    public function cerrarSinSolucion(string $tecnico, array $trabajosIntentados = [], ?DateTimeImmutable $fecha = null): void
    {
        $this->exigirTecnicoAsignado($tecnico);
        $this->exigirTransicion(self::SIN_SOLUCION);
        $this->motivoSinSolucion = self::SIN_REPARACION_POSIBLE;
        $this->trabajosRealizados = $trabajosIntentados;
        $this->fechaAlta = $fecha ?? new DateTimeImmutable();
        $this->pasarA(self::SIN_SOLUCION, $tecnico, $this->fechaAlta, 'No tiene reparación posible');
    }

    /** Entrega al cliente: con el recibo original o, si lo perdió, con su carnet (RN11). */
    public function entregar(string $usuario, string $verificacion, ?string $carnetPresentado = null, ?DateTimeImmutable $fecha = null): void
    {
        if (!in_array($verificacion, [self::CON_RECIBO, self::CON_CARNET], true)) {
            throw new InvalidArgumentException('La entrega se verifica con RECIBO o con CARNET.');
        }
        if ($verificacion === self::CON_CARNET && ($carnetPresentado === null || trim($carnetPresentado) === '')) {
            throw new DomainException('Sin recibo, el cliente debe presentar su carnet (se guarda una copia).');
        }
        $this->exigirTransicion(self::ENTREGADA);
        $this->entregadaPor = $usuario;
        $this->verificacionEntrega = $verificacion === self::CON_CARNET ? "CARNET {$carnetPresentado}" : self::CON_RECIBO;
        $this->fechaEntrega = $fecha ?? new DateTimeImmutable();
        $this->pasarA(self::ENTREGADA, $usuario, $this->fechaEntrega, "Verificado con {$this->verificacionEntrega}");
    }

    // =====================================================================
    //  Revisión diaria del plazo de 6 meses (RN10) — la dispara una tarea programada
    // =====================================================================

    public function venceEl(): DateTimeImmutable
    {
        return $this->fechaIngreso->modify(self::PLAZO_MAXIMO);
    }

    public function revisarPlazo(DateTimeImmutable $hoy): void
    {
        if ($this->estado === self::ENTREGADA) {
            return;
        }
        $vence = $this->venceEl();
        if ($hoy >= $vence) {
            $tipo = EventoOrden::PLAZO_VENCIDO;
        } elseif ($hoy >= $vence->modify('-' . self::AVISAR_DIAS_ANTES . ' days')) {
            $tipo = EventoOrden::PLAZO_POR_VENCER;
        } else {
            return;
        }
        $nota = sprintf('Lleva %d días en el taller; el plazo de 6 meses %s el %s.',
            $this->diasEnTaller($hoy), $tipo === EventoOrden::PLAZO_VENCIDO ? 'venció' : 'vence', $vence->format('d/m/Y'));
        $this->publicar(new EventoOrden($tipo, $this, 'sistema', $hoy, $this->estado, $this->estado, $nota));
    }

    // =====================================================================
    //  STRATEGY — la orden dice CÓMO terminó; cuánto cobrar lo decide una ReglaDeCobro
    // =====================================================================

    public const EN_CURSO = 'EN_CURSO';

    public function desenlace(): string
    {
        if ($this->fueSolucionada()) {
            return self::SOLUCIONADA;
        }
        return $this->motivoSinSolucion ?? self::EN_CURSO;
    }

    // =====================================================================
    //  Consultas
    // =====================================================================

    public function estado(): string { return $this->estado; }
    public function tecnico(): ?string { return $this->tecnico; }
    public function diagnostico(): ?string { return $this->diagnostico; }
    public function tipoTrabajo(): ?string { return $this->tipoTrabajo; }
    public function presupuesto(): float { return $this->presupuesto; }
    public function costoDiagnostico(): float { return $this->costoDiagnostico; }
    /** @return string[] */
    public function trabajosRealizados(): array { return $this->trabajosRealizados; }
    /** @return array<int, array{descripcion: string, precio: float}> */
    public function repuestos(): array { return $this->repuestos; }
    public function manoDeObra(): float { return $this->manoDeObra; }
    public function garantiaDias(): int { return $this->garantiaDias; }
    public function motivoSinSolucion(): ?string { return $this->motivoSinSolucion; }
    public function fechaAlta(): ?DateTimeImmutable { return $this->fechaAlta; }
    public function fechaEntrega(): ?DateTimeImmutable { return $this->fechaEntrega; }
    public function entregadaPor(): ?string { return $this->entregadaPor; }
    public function verificacionEntrega(): ?string { return $this->verificacionEntrega; }

    public function fueSolucionada(): bool
    {
        return $this->fechaAlta !== null && $this->motivoSinSolucion === null;
    }

    public function totalRepuestos(): float
    {
        return array_sum(array_column($this->repuestos, 'precio'));
    }

    public function diasEnTaller(DateTimeImmutable $hoy): int
    {
        return (int) $this->fechaIngreso->diff($hoy)->days;
    }

    public function resumen(): string
    {
        return sprintf('%s | %-22s | %s | %s',
            $this->numeroRecibo, $this->estado, self::rellenar($this->cliente->nombreCompleto, 26), $this->equipo->resumen());
    }

    /** str_pad que cuenta caracteres (no bytes), para que las tildes no desalineen la consola. */
    private static function rellenar(string $texto, int $ancho): string
    {
        return $texto . str_repeat(' ', max(0, $ancho - preg_match_all('/./u', $texto)));
    }

    // =====================================================================
    //  Reglas internas
    // =====================================================================

    private function exigirTransicion(string $nuevo): void
    {
        if (!in_array($nuevo, self::TRANSICIONES[$this->estado], true)) {
            throw new DomainException(sprintf('Orden %s: no se puede pasar de %s a %s.', $this->numeroRecibo, $this->estado, $nuevo));
        }
    }

    /** Cambia el estado y AVISA a los observadores (con los datos de la orden ya completos). */
    private function pasarA(string $nuevo, string $usuario, ?DateTimeImmutable $fecha, string $nota = ''): void
    {
        $this->exigirTransicion($nuevo);
        $anterior = $this->estado;
        $this->estado = $nuevo;
        $this->publicar(new EventoOrden(EventoOrden::CAMBIO_ESTADO, $this, $usuario, $fecha ?? new DateTimeImmutable(), $anterior, $nuevo, $nota));
    }

    /** OBSERVER — notificar: si un observador falla, el cambio de estado NO se pierde ni se revierte. */
    private function publicar(EventoOrden $evento): void
    {
        foreach ($this->observadores as $observador) {
            try {
                $observador->alOcurrir($evento);
            } catch (Throwable $e) {
                error_log(sprintf('[aviso fallido] %s no procesó %s de %s: %s',
                    $observador::class, $evento->tipo, $this->numeroRecibo, $e->getMessage()));
            }
        }
    }

    private function exigirTecnicoAsignado(string $tecnico): void
    {
        if ($this->tecnico !== $tecnico) {
            throw new DomainException(sprintf('Orden %s: solo el técnico asignado (%s) puede registrar trabajo en ella.',
                $this->numeroRecibo, $this->tecnico ?? 'nadie todavía'));
        }
    }
}
