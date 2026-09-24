<?php
declare(strict_types=1);

/**
 * h3/final — FUSIÓN Observer + Strategy en el cierre de órdenes.
 *
 *   OBSERVER : cuando una orden se cierra o se acerca al plazo, se enteran la bandeja
 *              de las secretarias y la bitácora del gerente.
 *   STRATEGY : cuánto se cobra depende del desenlace; la bandeja lo usa para el aviso
 *              y el mostrador para cobrar (misma regla, mismo monto).
 *
 * Ejecutar:  php h3/final/demo.php
 */
require_once __DIR__ . '/autoload.php';

$f = fn (string $s) => new DateTimeImmutable($s);
function titulo(string $t): void
{
    echo PHP_EOL . str_repeat('=', 90) . PHP_EOL . $t . PHP_EOL . str_repeat('=', 90) . PHP_EOL;
}

$taller = new Taller();

// ------------------------------------------------------------------------------------
titulo('1) RECEPCIÓN — cada orden que entra se "abre" en el taller (se le suscriben los observadores)');
// ------------------------------------------------------------------------------------
$juan  = new Cliente('Juan Carlos Mamani Quispe', '71234567', '65432109');
$maria = new Cliente('María Fernanda Rojas Vaca', '76543210');
$sonia = new Cliente('Sonia Beatriz Quiroga Lima', '67001122');

$o801 = $taller->abrir(new OrdenDeTrabajo('R-000801', $juan,
    new Laptop('HP', '15-dy2021la', 'Core i5-1135G7', 8, 0, 256, Laptop::BATERIA_INTERNA),
    ['NO_ENCIENDE'], 'ana', $f('2026-09-21 09:10'), 'Se apagó y no prende', ['Falta 1 perno'], ['fotos/R-000801-1.jpg'], ['cargador']));
$o802 = $taller->abrir(new OrdenDeTrabajo('R-000802', $maria,
    new Laptop('Lenovo', 'IdeaPad 3', 'Ryzen 5 5500U', 8, 0, 512, Laptop::BATERIA_INTERNA),
    ['INGRESO_LIQUIDO'], 'ana', $f('2026-09-21 10:30'), 'Café derramado', [], ['fotos/R-000802-1.jpg']));
$o803 = $taller->abrir(new OrdenDeTrabajo('R-000803', $maria,
    new PcEscritorio('Ensamblada', 'Torre ATX', 'Core i3-10100', 8, 1000, 0),
    ['FALLA_SOFTWARE'], 'carlos', $f('2026-09-21 10:35'), 'Windows no arranca', [], ['fotos/R-000803-1.jpg']));
$o804 = $taller->abrir(new OrdenDeTrabajo('R-000804', $juan,
    new Laptop('Toshiba', 'Satellite C55', 'Core i3-4005U', 4, 500, 0, Laptop::BATERIA_EXTERNA),
    ['NO_ENCIENDE'], 'ana', $f('2026-09-22 08:50'), '', ['Carcasa rajada'], ['fotos/R-000804-1.jpg']));
// Una orden vieja cargada "de la base de datos": solucionada en abril y nadie vino a recogerla.
$o650 = $taller->abrir(new OrdenDeTrabajo('R-000650', $sonia,
    new PcEscritorio('Dell', 'Vostro 3888', 'Core i3-10100', 4, 1000, 0),
    ['MANTENIMIENTO'], 'rosa', $f('2026-04-10 11:00'), '', [], ['fotos/R-000650-1.jpg'], [], true));

$ordenes = [$o801, $o802, $o803, $o804, $o650];
foreach ($ordenes as $o) {
    echo $o->resumen() . PHP_EOL;
}

// ------------------------------------------------------------------------------------
titulo('2) TALLER — los técnicos trabajan; cada cambio de estado se publica solo');
// ------------------------------------------------------------------------------------
$o650->tomar('carlos', $f('2026-04-11 09:00'));
$o650->registrarDiagnostico('carlos', 'Limpieza y pasta térmica', OrdenDeTrabajo::TRABAJO_MANTENIMIENTO, 100.0, 0.0, $f('2026-04-11 10:00'));
$o650->registrarSolucion('carlos', ['Mantenimiento preventivo'], [], 100.0, 15, $f('2026-04-11 15:00'));

$o801->tomar('carlos', $f('2026-09-22 08:30'));
$o801->registrarDiagnostico('carlos', 'IC de carga quemado', OrdenDeTrabajo::TRABAJO_ELECTRONICA, 350.0, 50.0, $f('2026-09-22 10:00'));
$o801->registrarRespuestaCliente('carlos', true, $f('2026-09-22 10:15'));
$o801->registrarSolucion('carlos', ['Cambio de IC de carga', 'Limpieza de placa'],
    [['descripcion' => 'IC de carga BQ24780S', 'precio' => 120.0]], 230.0, 30, $f('2026-09-23 16:00'));

$o802->tomar('miguel', $f('2026-09-22 09:00'));
$o802->registrarDiagnostico('miguel', 'Placa sulfatada', OrdenDeTrabajo::TRABAJO_ELECTRONICA, 480.0, 50.0, $f('2026-09-22 12:00'));
$o802->registrarRespuestaCliente('miguel', false, $f('2026-09-22 12:20'));

$o803->tomar('carlos', $f('2026-09-22 11:00'));
$o803->registrarDiagnostico('carlos', 'Sistema dañado; requiere reinstalar', OrdenDeTrabajo::TRABAJO_SOFTWARE, 150.0, 30.0, $f('2026-09-22 11:40'));
$o803->registrarRespuestaCliente('carlos', false, $f('2026-09-22 12:00'));

foreach ($ordenes as $o) {
    echo $o->resumen() . PHP_EOL;
}

// ------------------------------------------------------------------------------------
titulo('3) TAREA PROGRAMADA DE LA MAÑANA — revisión del plazo de 6 meses');
// ------------------------------------------------------------------------------------
$taller->revisarPlazos($ordenes, $f('2026-09-24 07:00'));
echo 'Revisadas ' . count($ordenes) . ' órdenes.' . PHP_EOL;

// ------------------------------------------------------------------------------------
titulo('4) BANDEJA DE LA SECRETARIA — a quién llamar y cuánto cobrar (Observer + Strategy)');
// ------------------------------------------------------------------------------------
foreach ($taller->bandeja->pendientes() as $aviso) {
    echo '• ' . $aviso['mensaje'] . PHP_EOL;
}

// ------------------------------------------------------------------------------------
titulo('5) MOSTRADOR — llegan los clientes: se cobra con la MISMA regla del aviso');
// ------------------------------------------------------------------------------------
// Juan trae su recibo y paga con QR.
$cobro = $taller->mostrador->entregar($o801, 'ana', Mostrador::QR, OrdenDeTrabajo::CON_RECIBO, null, $f('2026-09-24 10:00'));
printf("R-000801 entregada a Juan: Bs %.2f por QR%s", $cobro->total(), PHP_EOL);

// María perdió el recibo: la secretaria la busca por celular y la entrega es con carnet.
$deMaria = array_filter($ordenes, fn (OrdenDeTrabajo $o) => $o->cliente->coincideCon('76543210'));
foreach ($deMaria as $o) {
    $cobro = $taller->mostrador->entregar($o, 'rosa', Mostrador::EFECTIVO, OrdenDeTrabajo::CON_CARNET, '6543210 SC', $f('2026-09-24 11:30'));
    $pago = $cobro->total() > 0 ? sprintf('Bs %.2f en efectivo', $cobro->total()) : 'sin cobro';
    printf("%s entregada a María (carnet): %s — %s%s", $o->numeroRecibo, $pago, $cobro->detalle(), PHP_EOL);
}

// ------------------------------------------------------------------------------------
titulo('6) CIERRE DE CAJA DEL DÍA (RF6)');
// ------------------------------------------------------------------------------------
$cierre = $taller->mostrador->cierreDeCaja();
foreach ($cierre['movimientos'] as $m) {
    printf("  %s  %-6s  %-9s Bs %7.2f   %s%s", $m['recibo'], $m['usuario'], $m['metodo'], $m['monto'], $m['detalle'], PHP_EOL);
}
printf("  Efectivo en caja: Bs %.2f | QR (en el banco): Bs %.2f | Total: Bs %.2f%s", $cierre['EFECTIVO'], $cierre['QR'], $cierre['total'], PHP_EOL);

// ------------------------------------------------------------------------------------
titulo('7) EL GERENTE CAMBIA LA POLÍTICA — ahora "sin reparación posible" cobra el diagnóstico electrónico');
// ------------------------------------------------------------------------------------
$taller->calculadora->usarRegla(OrdenDeTrabajo::SIN_REPARACION_POSIBLE, new CobroPorDiagnostico());
// Ni OrdenDeTrabajo, ni la bandeja, ni el mostrador se tocaron. El siguiente cierre ya usa la regla nueva:
$o804->tomar('miguel', $f('2026-09-24 12:00'));
$o804->registrarDiagnostico('miguel', 'Placa madre en corto, sin repuesto disponible', OrdenDeTrabajo::TRABAJO_ELECTRONICA, 0.0, 50.0, $f('2026-09-24 13:00'));
$o804->cerrarSinSolucion('miguel', ['Revisión de línea de alimentación'], $f('2026-09-24 13:10'));
$ultimo = array_values($taller->bandeja->pendientes());
echo '• ' . end($ultimo)['mensaje'] . PHP_EOL;

// ------------------------------------------------------------------------------------
titulo('8) BITÁCORA DEL GERENTE (Observer) y trabajos por técnico (RF6)');
// ------------------------------------------------------------------------------------
foreach ($taller->bitacora->lineas() as $linea) {
    echo $linea . PHP_EOL;
}
echo PHP_EOL;
foreach (['carlos', 'miguel'] as $tecnico) {
    $trabajos = $taller->bitacora->trabajosDe($tecnico, $f('2026-09-01'), $f('2026-09-30 23:59'));
    echo "Septiembre, {$tecnico}: " . count($trabajos) . ' órdenes cerradas → ' . implode(', ', $trabajos) . PHP_EOL;
}
