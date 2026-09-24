<?php
declare(strict_types=1);

/**
 * Pruebas automáticas de la fusión Observer + Strategy (sin frameworks: solo PHP).
 * Ejecutar:  php h3/final/pruebas.php      → código de salida 0 si todo pasa.
 */
require_once __DIR__ . '/autoload.php';

$fallas = 0;
$total  = 0;
function prueba(string $nombre, callable $cuerpo): void
{
    global $fallas, $total;
    $total++;
    try {
        $cuerpo();
        echo "  OK     {$nombre}" . PHP_EOL;
    } catch (Throwable $e) {
        $fallas++;
        echo "  FALLA  {$nombre}: {$e->getMessage()}" . PHP_EOL;
    }
}
function afirmar(bool $condicion, string $mensaje): void
{
    if (!$condicion) {
        throw new RuntimeException($mensaje);
    }
}
function lanza(callable $accion, string $clase): void
{
    try {
        $accion();
    } catch (Throwable $e) {
        afirmar($e instanceof $clase, 'se esperaba ' . $clase . ' y se obtuvo ' . get_class($e));
        return;
    }
    throw new RuntimeException("se esperaba {$clase} y no se lanzó nada");
}

/** Observador espía para las pruebas: guarda todo lo que recibe. */
final class Espia implements ObservadorDeOrden
{
    /** @var EventoOrden[] */
    public array $recibidos = [];
    public function alOcurrir(EventoOrden $evento): void { $this->recibidos[] = $evento; }
}
/** Observador que siempre falla. */
final class ObservadorRoto implements ObservadorDeOrden
{
    public function alOcurrir(EventoOrden $evento): void { throw new RuntimeException('se cayó la conexión'); }
}

function laptop(string $recibo = 'R-000900', string $ingreso = '2026-09-20 09:00', bool $pre = false, string $motivo = 'NO_ENCIENDE'): OrdenDeTrabajo
{
    return new OrdenDeTrabajo($recibo, new Cliente('Cliente De Prueba', '70000001', '60000002'),
        new Laptop('HP', '14-dq', 'Core i3', 8, 0, 256, Laptop::BATERIA_INTERNA),
        [$motivo], 'ana', new DateTimeImmutable($ingreso), '', [], ['f.jpg'], [], $pre);
}
function diagnosticada(Taller $t, string $tipo, float $costoDiag = 50.0): OrdenDeTrabajo
{
    $o = $t->abrir(laptop());
    $o->tomar('carlos');
    $o->registrarDiagnostico('carlos', 'diag', $tipo, 300.0, $costoDiag);
    return $o;
}

echo PHP_EOL . 'Flujo de estados (RF3)' . PHP_EOL;
prueba('no se puede entregar una orden recién recibida', function () {
    lanza(fn () => laptop()->entregar('ana', OrdenDeTrabajo::CON_RECIBO), DomainException::class);
});
prueba('no se puede dar de alta sin haber diagnosticado', function () {
    $o = laptop();
    $o->tomar('carlos');
    lanza(fn () => $o->registrarSolucion('carlos', ['x'], [], 100.0, 30), DomainException::class);
});
prueba('solo el técnico asignado registra trabajo en la orden', function () {
    $o = laptop();
    $o->tomar('carlos');
    lanza(fn () => $o->registrarDiagnostico('miguel', 'x', OrdenDeTrabajo::TRABAJO_SOFTWARE, 0.0), DomainException::class);
});
prueba('sin recibo, la entrega exige carnet', function () {
    $t = new Taller();
    $o = diagnosticada($t, OrdenDeTrabajo::TRABAJO_ELECTRONICA);
    $o->registrarRespuestaCliente('carlos', false);
    lanza(fn () => $t->mostrador->entregar($o, 'ana', Mostrador::EFECTIVO, OrdenDeTrabajo::CON_CARNET), DomainException::class);
    afirmar($o->estado() === OrdenDeTrabajo::SIN_SOLUCION, 'la orden no debió cambiar de estado');
    afirmar($t->mostrador->cierreDeCaja()['total'] === 0.0, 'no debió registrarse ningún cobro');
});

echo PHP_EOL . 'Strategy: cuánto se cobra según el desenlace' . PHP_EOL;
prueba('reparada: mano de obra + repuestos', function () {
    $t = new Taller();
    $o = diagnosticada($t, OrdenDeTrabajo::TRABAJO_ELECTRONICA);
    $o->registrarRespuestaCliente('carlos', true);
    $o->registrarSolucion('carlos', ['Cambio'], [['descripcion' => 'IC', 'precio' => 120.0], ['descripcion' => 'Pasta', 'precio' => 25.0]], 200.0, 30);
    afirmar($t->calculadora->cobroDe($o)->total() === 345.0, 'debía cobrar 345');
});
prueba('rechazo con falla electrónica: cobra el diagnóstico (RN7)', function () {
    $t = new Taller();
    $o = diagnosticada($t, OrdenDeTrabajo::TRABAJO_ELECTRONICA, 50.0);
    $o->registrarRespuestaCliente('carlos', false);
    afirmar($t->calculadora->cobroDe($o)->total() === 50.0, 'debía cobrar 50');
});
prueba('rechazo con falla de software: no cobra', function () {
    $t = new Taller();
    $o = diagnosticada($t, OrdenDeTrabajo::TRABAJO_SOFTWARE, 30.0);
    $o->registrarRespuestaCliente('carlos', false);
    afirmar($t->calculadora->cobroDe($o)->total() === 0.0, 'no debía cobrar');
});
prueba('sin reparación posible: sin cargo', function () {
    $t = new Taller();
    $o = diagnosticada($t, OrdenDeTrabajo::TRABAJO_ELECTRONICA);
    $o->registrarRespuestaCliente('carlos', true);
    $o->cerrarSinSolucion('carlos');
    afirmar($t->calculadora->cobroDe($o)->regla === 'Sin cargo', 'debía aplicarse SinCargo');
});
prueba('una orden en curso no se puede cobrar', function () {
    $t = new Taller();
    lanza(fn () => $t->calculadora->cobroDe(diagnosticada($t, OrdenDeTrabajo::TRABAJO_SOFTWARE)), DomainException::class);
});

echo PHP_EOL . 'Observer: quién se entera' . PHP_EOL;
prueba('cada transición llega a los observadores con el usuario que la hizo', function () {
    $o = laptop();
    $espia = new Espia();
    $o->suscribir($espia);
    $o->tomar('carlos');
    $o->registrarDiagnostico('carlos', 'x', OrdenDeTrabajo::TRABAJO_SOFTWARE, 100.0);
    afirmar(count($espia->recibidos) === 2, 'debían llegar 2 eventos');
    afirmar($espia->recibidos[1]->usuario === 'carlos', 'el evento debía traer al técnico');
    afirmar($espia->recibidos[1]->estadoNuevo === OrdenDeTrabajo::ESPERANDO_AUTORIZACION, 'estado nuevo incorrecto');
});
prueba('el observador ve la orden ya completa (datos antes que el aviso)', function () {
    $o = laptop();
    $o->tomar('carlos');
    $o->registrarDiagnostico('carlos', 'x', OrdenDeTrabajo::TRABAJO_ELECTRONICA, 100.0);
    $o->registrarRespuestaCliente('carlos', true);
    $mirador = new class implements ObservadorDeOrden {
        public ?float $manoDeObraVista = null;
        public function alOcurrir(EventoOrden $e): void { $this->manoDeObraVista = $e->orden->manoDeObra(); }
    };
    $o->suscribir($mirador);
    $o->registrarSolucion('carlos', ['x'], [], 180.0, 30);
    afirmar($mirador->manoDeObraVista === 180.0, 'el observador vio la orden a medio llenar');
});
prueba('un observador desuscrito deja de recibir eventos', function () {
    $o = laptop();
    $espia = new Espia();
    $o->suscribir($espia);
    $o->tomar('carlos');
    $o->desuscribir($espia);
    $o->registrarDiagnostico('carlos', 'x', OrdenDeTrabajo::TRABAJO_SOFTWARE, 100.0);
    afirmar(count($espia->recibidos) === 1, 'no debía recibir el segundo evento');
});
prueba('si un observador falla, el estado cambia igual y los demás se enteran', function () {
    $o = laptop();
    $espia = new Espia();
    $o->suscribir(new ObservadorRoto());
    $o->suscribir($espia);
    $logAnterior = ini_set('error_log', sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pruebas-taller.log');   // silencia el aviso esperado
    $o->tomar('carlos');
    ini_set('error_log', (string) $logAnterior);
    afirmar($o->estado() === OrdenDeTrabajo::EN_DIAGNOSTICO, 'el estado debía cambiar');
    afirmar(count($espia->recibidos) === 1, 'el segundo observador debía enterarse');
});
prueba('plazo: 92 días no avisa, 160 días POR VENCER, 190 días VENCIDO', function () {
    $o = laptop('R-000901', '2026-03-01 09:00');
    $espia = new Espia();
    $o->suscribir($espia);
    $o->revisarPlazo(new DateTimeImmutable('2026-06-01'));   // 92 días: nada
    $o->revisarPlazo(new DateTimeImmutable('2026-08-08'));   // 160 días
    $o->revisarPlazo(new DateTimeImmutable('2026-09-07'));   // 190 días
    $tipos = array_map(fn (EventoOrden $e) => $e->tipo, $espia->recibidos);
    afirmar($tipos === [EventoOrden::PLAZO_POR_VENCER, EventoOrden::PLAZO_VENCIDO], 'eventos de plazo incorrectos: ' . implode(',', $tipos));
});
prueba('plazo: una orden ya entregada no genera alerta', function () {
    $t = new Taller();
    $o = $t->abrir(laptop('R-000902', '2026-03-01 09:00'));
    $o->tomar('carlos');
    $o->registrarDiagnostico('carlos', 'x', OrdenDeTrabajo::TRABAJO_ELECTRONICA, 100.0, 50.0);
    $o->registrarRespuestaCliente('carlos', false);
    $t->mostrador->entregar($o, 'ana', Mostrador::EFECTIVO, OrdenDeTrabajo::CON_RECIBO);
    $espia = new Espia();
    $o->suscribir($espia);
    $o->revisarPlazo(new DateTimeImmutable('2026-09-20'));
    afirmar($espia->recibidos === [], 'no debía avisar sobre una orden entregada');
});

echo PHP_EOL . 'Fusión: Observer + Strategy trabajando juntos' . PHP_EOL;
prueba('al dar de alta, la bandeja avisa con el monto que calcula la estrategia', function () {
    $t = new Taller();
    $o = diagnosticada($t, OrdenDeTrabajo::TRABAJO_ELECTRONICA);
    $o->registrarRespuestaCliente('carlos', true);
    $o->registrarSolucion('carlos', ['Cambio'], [['descripcion' => 'IC', 'precio' => 120.0]], 230.0, 30);
    $avisos = array_values($t->bandeja->pendientes());
    afirmar(count($avisos) === 1, 'debía haber exactamente 1 aviso');
    afirmar(str_contains($avisos[0]['mensaje'], 'Bs 350.00'), 'el aviso no trae el monto: ' . $avisos[0]['mensaje']);
});
prueba('el aviso y el mostrador cobran lo mismo', function () {
    $t = new Taller();
    $o = diagnosticada($t, OrdenDeTrabajo::TRABAJO_ELECTRONICA, 50.0);
    $o->registrarRespuestaCliente('carlos', false);
    $aviso = array_values($t->bandeja->pendientes())[0]['mensaje'];
    $cobro = $t->mostrador->entregar($o, 'ana', Mostrador::QR, OrdenDeTrabajo::CON_RECIBO);
    afirmar(str_contains($aviso, sprintf('Bs %.2f', $cobro->total())), 'el aviso y el cobro no coinciden');
});
prueba('cambiar la regla en caliente cambia el aviso sin tocar la orden ni la bandeja', function () {
    $t = new Taller();
    $t->calculadora->usarRegla(OrdenDeTrabajo::SIN_REPARACION_POSIBLE, new CobroPorDiagnostico());
    $o = diagnosticada($t, OrdenDeTrabajo::TRABAJO_ELECTRONICA, 50.0);
    $o->registrarRespuestaCliente('carlos', true);
    $o->cerrarSinSolucion('carlos');
    $aviso = array_values($t->bandeja->pendientes())[0]['mensaje'];
    afirmar(str_contains($aviso, 'Cobrar Bs 50.00'), 'el aviso no refleja la nueva política: ' . $aviso);
});
prueba('si falta una regla de cobro, el aviso llega igual (sin monto)', function () {
    $t = new Taller();
    $sinReglas = new CalculadoraDeCobro([]);
    $bandeja = new BandejaDeAvisos($sinReglas);
    $o = diagnosticada($t, OrdenDeTrabajo::TRABAJO_SOFTWARE);
    $o->suscribir($bandeja);
    $o->registrarRespuestaCliente('carlos', false);
    $avisos = array_values($bandeja->pendientes());
    afirmar(count($avisos) === 1 && str_contains($avisos[0]['mensaje'], 'por confirmar'), 'el aviso se perdió');
});
prueba('la bitácora registra la entrega con el usuario que cobró', function () {
    $t = new Taller();
    $o = diagnosticada($t, OrdenDeTrabajo::TRABAJO_ELECTRONICA);
    $o->registrarRespuestaCliente('carlos', false);
    $t->mostrador->entregar($o, 'rosa', Mostrador::EFECTIVO, OrdenDeTrabajo::CON_RECIBO);
    $ultima = $t->bitacora->lineas()[count($t->bitacora->lineas()) - 1];
    afirmar(str_contains($ultima, 'rosa') && str_contains($ultima, 'ENTREGADA'), 'la entrega no quedó en la bitácora: ' . $ultima);
});
prueba('cierre de caja separa efectivo de QR', function () {
    $t = new Taller();
    foreach ([Mostrador::EFECTIVO, Mostrador::QR] as $metodo) {
        $o = diagnosticada($t, OrdenDeTrabajo::TRABAJO_ELECTRONICA, 50.0);
        $o->registrarRespuestaCliente('carlos', false);
        $t->mostrador->entregar($o, 'ana', $metodo, OrdenDeTrabajo::CON_RECIBO);
    }
    $cierre = $t->mostrador->cierreDeCaja();
    afirmar($cierre['EFECTIVO'] === 50.0 && $cierre['QR'] === 50.0 && $cierre['total'] === 100.0, 'cierre incorrecto');
});

echo PHP_EOL . str_repeat('-', 60) . PHP_EOL;
echo $fallas === 0 ? "TODO OK: {$total} pruebas pasaron." : "{$fallas} de {$total} pruebas FALLARON.";
echo PHP_EOL;
exit($fallas === 0 ? 0 : 1);
