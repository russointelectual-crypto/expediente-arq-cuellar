<?php
declare(strict_types=1);

/**
 * BASE sin patrones — un día normal en el taller.
 * Ejecutar:  php h3/base/demo.php
 */
require_once __DIR__ . '/Cliente.php';
require_once __DIR__ . '/Equipo.php';
require_once __DIR__ . '/Laptop.php';
require_once __DIR__ . '/PcEscritorio.php';
require_once __DIR__ . '/OrdenDeTrabajo.php';

/**
 * Así se crea el equipo en la base: el que registra decide con un if cuál clase instanciar.
 * (Este if es justamente lo que el laboratorio con-factory/ saca de aquí.)
 */
function equipoDesdeFormulario(array $f): Equipo
{
    if ($f['tipo'] === 'laptop') {
        return new Laptop($f['marca'], $f['modelo'], $f['procesador'], $f['ram'], $f['hdd'], $f['ssd'], $f['bateria']);
    } elseif ($f['tipo'] === 'pc') {
        return new PcEscritorio($f['marca'], $f['modelo'], $f['procesador'], $f['ram'], $f['hdd'], $f['ssd']);
    }
    throw new InvalidArgumentException("Tipo de equipo desconocido: {$f['tipo']}");
}

function titulo(string $texto): void
{
    echo PHP_EOL . str_repeat('=', 78) . PHP_EOL . $texto . PHP_EOL . str_repeat('=', 78) . PHP_EOL;
}

$fecha = fn (string $f) => new DateTimeImmutable($f);

// ---------------------------------------------------------------------------
titulo('1) RECEPCIÓN — la secretaria (ana) registra 3 equipos de 2 clientes');
// ---------------------------------------------------------------------------
$juan  = new Cliente('Juan Carlos Mamani Quispe', '71234567', '65432109');
$maria = new Cliente('María Fernanda Rojas Vaca', '76543210');

// Constructor largo: 11 parámetros, varios opcionales (esto lo ataca con-builder/).
$o101 = new OrdenDeTrabajo(
    'R-000101', $juan,
    equipoDesdeFormulario(['tipo' => 'laptop', 'marca' => 'HP', 'modelo' => '15-dy2021la', 'procesador' => 'Core i5-1135G7',
        'ram' => 8, 'hdd' => 0, 'ssd' => 256, 'bateria' => Laptop::BATERIA_INTERNA]),
    ['NO_ENCIENDE'], 'ana', $fecha('2026-09-01 09:10'),
    'Se apagó de golpe y ya no prende', ['Falta 1 perno inferior', 'Rayón en la tapa'], ['fotos/R-000101-1.jpg'], ['cargador'],
);
// María deja DOS equipos → dos órdenes, cada una con su propio número (RN1).
$o102 = new OrdenDeTrabajo(
    'R-000102', $maria,
    equipoDesdeFormulario(['tipo' => 'laptop', 'marca' => 'Lenovo', 'modelo' => 'IdeaPad 3', 'procesador' => 'Ryzen 5 5500U',
        'ram' => 8, 'hdd' => 0, 'ssd' => 512, 'bateria' => Laptop::BATERIA_INTERNA]),
    ['INGRESO_LIQUIDO', 'TECLADO'], 'ana', $fecha('2026-09-01 10:30'),
    'Se le derramó café', ['Teclado pegajoso'], ['fotos/R-000102-1.jpg'],
);
$o103 = new OrdenDeTrabajo(
    'R-000103', $maria,
    equipoDesdeFormulario(['tipo' => 'pc', 'marca' => 'Ensamblada', 'modelo' => 'Torre ATX', 'procesador' => 'Core i3-10100',
        'ram' => 8, 'hdd' => 1000, 'ssd' => 0]),
    ['UPGRADE_SSD'], 'ana', $fecha('2026-09-01 10:35'),
    'Quiere que sea más rápida, sin perder sus archivos', [], ['fotos/R-000103-1.jpg'], [], true,
);
$ordenes = [$o101, $o102, $o103];
foreach ($ordenes as $o) {
    echo $o->resumen() . PHP_EOL;
    foreach ($o->equipo->notasDeRecepcion() as $nota) {
        echo "           nota: {$nota}" . PHP_EOL;
    }
}

// ---------------------------------------------------------------------------
titulo('2) TALLER — los técnicos toman las órdenes por número de recibo');
// ---------------------------------------------------------------------------
$o101->tomar('carlos');
$o101->registrarDiagnostico('carlos', 'Corto en el circuito de carga (IC de carga quemado)', OrdenDeTrabajo::TRABAJO_ELECTRONICA, 350.0, 50.0);
$o101->registrarRespuestaCliente('carlos', true);  // Juan autoriza por teléfono
$o101->registrarSolucion('carlos',
    ['Cambio de IC de carga', 'Limpieza de placa'],
    [['descripcion' => 'IC de carga BQ24780S', 'precio' => 120.0]],
    230.0, 30, $fecha('2026-09-03 16:00'));

$o102->tomar('miguel');
$o102->registrarDiagnostico('miguel', 'Placa sulfatada por líquido, requiere cambio de teclado y limpieza química', OrdenDeTrabajo::TRABAJO_ELECTRONICA, 480.0, 50.0);
$o102->registrarRespuestaCliente('miguel', false, $fecha('2026-09-02 11:00'));  // María no autoriza

$o103->tomar('carlos');
$o103->registrarDiagnostico('carlos', 'Disco HDD lento; se clona a SSD', OrdenDeTrabajo::TRABAJO_ACTUALIZACION, 360.0);  // pre-autorizada → EN_REPARACION
$o103->registrarSolucion('carlos',
    ['Clonación de HDD a SSD', 'Configuración de arranque desde SSD'],
    [['descripcion' => 'SSD SATA 480 GB', 'precio' => 280.0]],
    80.0, 30, $fecha('2026-09-02 18:00'));

foreach ($ordenes as $o) {
    printf('%s  → a cobrar: Bs %7.2f%s', $o->resumen(), $o->totalACobrar(), PHP_EOL);
}

// ---------------------------------------------------------------------------
titulo('3) BÚSQUEDA (RF2) — María perdió su recibo y viene solo con su celular');
// ---------------------------------------------------------------------------
$encontradas = array_filter($ordenes, fn (OrdenDeTrabajo $o) => $o->cliente->coincideCon('76543210'));
foreach ($encontradas as $o) {
    echo 'encontrada: ' . $o->resumen() . PHP_EOL;
}

// ---------------------------------------------------------------------------
titulo('4) ENTREGA — con recibo (Juan) y con carnet (María)');
// ---------------------------------------------------------------------------
$o101->entregar('ana', OrdenDeTrabajo::CON_RECIBO, null, $fecha('2026-09-04 10:00'));
$o102->entregar('ana', OrdenDeTrabajo::CON_CARNET, '6543210 SC', $fecha('2026-09-04 12:00'));
$o103->entregar('ana', OrdenDeTrabajo::CON_CARNET, '6543210 SC', $fecha('2026-09-04 12:00'));
foreach ($ordenes as $o) {
    printf('%s | entregada por %s con %s | cobrado Bs %.2f%s',
        $o->numeroRecibo, $o->entregadaPor(), $o->verificacionEntrega(), $o->totalACobrar(), PHP_EOL);
}

// ---------------------------------------------------------------------------
titulo('5) REGLAS QUE EL SISTEMA NO DEJA ROMPER');
// ---------------------------------------------------------------------------
$intentos = [
    'Entregar un equipo recién recibido' => function () use ($juan, $fecha) {
        $o = new OrdenDeTrabajo('R-000104', $juan, new PcEscritorio('Dell', 'OptiPlex 3080', 'Core i5-10500', 8, 0, 256),
            ['MANTENIMIENTO'], 'ana', $fecha('2026-09-04 09:00'), '', [], ['fotos/R-000104-1.jpg']);
        $o->entregar('ana', OrdenDeTrabajo::CON_RECIBO);
    },
    'Que otro técnico registre trabajo en una orden ajena' => function () use ($juan, $fecha) {
        $o = new OrdenDeTrabajo('R-000105', $juan, new PcEscritorio('Dell', 'OptiPlex 3080', 'Core i5-10500', 8, 0, 256),
            ['MANTENIMIENTO'], 'ana', $fecha('2026-09-04 09:00'), '', [], ['fotos/R-000105-1.jpg'], [], true);
        $o->tomar('carlos');
        $o->registrarDiagnostico('miguel', 'Limpieza', OrdenDeTrabajo::TRABAJO_MANTENIMIENTO, 100.0);
    },
    'Registrar una orden sin foto' => function () use ($juan, $fecha) {
        new OrdenDeTrabajo('R-000106', $juan, new PcEscritorio('HP', 'ProDesk', 'Core i3', 4, 500, 0),
            ['NO_ENCIENDE'], 'ana', $fecha('2026-09-04 09:00'));
    },
    'Celular con 7 dígitos' => fn () => new Cliente('Pedro Flores', '7123456'),
];
foreach ($intentos as $caso => $accion) {
    try {
        $accion();
        echo "[!] {$caso}: el sistema lo permitió (NO debería)" . PHP_EOL;
    } catch (Throwable $e) {
        echo "[rechazado] {$caso}: {$e->getMessage()}" . PHP_EOL;
    }
}
