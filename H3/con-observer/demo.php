<?php
declare(strict_types=1);

/**
 * con-observer — la base + OBSERVER: cuando una orden cambia, se enteran los interesados.
 * Ejecutar:  php h3/con-observer/demo.php
 */
foreach (['Cliente', 'Equipo', 'Laptop', 'PcEscritorio', 'EventoOrden', 'ObservadorDeOrden',
          'OrdenDeTrabajo', 'BandejaDeAvisos', 'BitacoraDeAuditoria'] as $clase) {
    require_once __DIR__ . "/{$clase}.php";
}

$f = fn (string $s) => new DateTimeImmutable($s);

// Los observadores se crean UNA vez (punto de composición) y se suscriben a cada orden.
$bandeja  = new BandejaDeAvisos();
$bitacora = new BitacoraDeAuditoria();
$abrir = function (OrdenDeTrabajo $o) use ($bandeja, $bitacora): OrdenDeTrabajo {
    $o->suscribir($bandeja);
    $o->suscribir($bitacora);
    return $o;
};

$juan  = new Cliente('Juan Carlos Mamani Quispe', '71234567', '65432109');
$maria = new Cliente('María Fernanda Rojas Vaca', '76543210');
$sonia = new Cliente('Sonia Beatriz Quiroga Lima', '67001122');

$o501 = $abrir(new OrdenDeTrabajo('R-000501', $juan,
    new Laptop('HP', '15-dy2021la', 'Core i5-1135G7', 8, 0, 256, Laptop::BATERIA_INTERNA),
    ['NO_ENCIENDE'], 'ana', $f('2026-09-20 09:10'), '', [], ['fotos/R-000501-1.jpg']));
$o502 = $abrir(new OrdenDeTrabajo('R-000502', $maria,
    new Laptop('Lenovo', 'IdeaPad 3', 'Ryzen 5 5500U', 8, 0, 512, Laptop::BATERIA_INTERNA),
    ['INGRESO_LIQUIDO'], 'ana', $f('2026-09-20 10:30'), '', [], ['fotos/R-000502-1.jpg']));
// Dos equipos viejos que el cliente nunca vino a recoger (para la revisión de plazos).
$o410 = $abrir(new OrdenDeTrabajo('R-000410', $sonia, new PcEscritorio('Dell', 'Vostro 3888', 'Core i3-10100', 4, 1000, 0),
    ['MANTENIMIENTO'], 'rosa', $f('2026-04-10 11:00'), '', [], ['fotos/R-000410-1.jpg'], [], true));
$o350 = $abrir(new OrdenDeTrabajo('R-000350', $sonia, new PcEscritorio('HP', 'Pavilion 590', 'Ryzen 3 3200G', 8, 1000, 0),
    ['NO_ENCIENDE'], 'rosa', $f('2026-03-02 16:00'), '', [], ['fotos/R-000350-1.jpg']));

// ---------------- El trabajo del día ----------------
$o501->tomar('carlos', $f('2026-09-21 08:30'));
$o501->registrarDiagnostico('carlos', 'IC de carga quemado', OrdenDeTrabajo::TRABAJO_ELECTRONICA, 350.0, 50.0, $f('2026-09-21 10:00'));
$o501->registrarRespuestaCliente('carlos', true, $f('2026-09-21 10:15'));
$o501->registrarSolucion('carlos', ['Cambio de IC de carga'], [['descripcion' => 'IC de carga BQ24780S', 'precio' => 120.0]],
    230.0, 30, $f('2026-09-22 16:00'));

$o502->tomar('miguel', $f('2026-09-21 09:00'));
$o502->registrarDiagnostico('miguel', 'Placa sulfatada', OrdenDeTrabajo::TRABAJO_ELECTRONICA, 480.0, 50.0, $f('2026-09-21 12:00'));
$o502->registrarRespuestaCliente('miguel', false, $f('2026-09-21 12:20'));

$o350->tomar('miguel', $f('2026-03-03 09:00'));
$o350->registrarDiagnostico('miguel', 'Placa madre en corto', OrdenDeTrabajo::TRABAJO_ELECTRONICA, 650.0, 50.0, $f('2026-03-03 12:00'));
$o350->registrarRespuestaCliente('miguel', false, $f('2026-03-03 12:30'));

$o410->tomar('carlos', $f('2026-04-11 09:00'));
$o410->registrarDiagnostico('carlos', 'Limpieza y cambio de pasta térmica', OrdenDeTrabajo::TRABAJO_MANTENIMIENTO, 100.0, 0.0, $f('2026-04-11 10:00'));
$o410->registrarSolucion('carlos', ['Mantenimiento preventivo'], [], 100.0, 15, $f('2026-04-11 15:00'));

// ---------------- La tarea programada de cada mañana ----------------
$hoy = $f('2026-09-24 07:00');
foreach ([$o501, $o502, $o410, $o350] as $orden) {
    $orden->revisarPlazo($hoy);
}

echo "== BANDEJA DE LA SECRETARIA (lo que tiene que llamar hoy) ==" . PHP_EOL;
foreach ($bandeja->pendientes() as $aviso) {
    echo '• ' . $aviso['mensaje'] . PHP_EOL;
}

echo PHP_EOL . "== BITÁCORA DEL GERENTE (quién hizo qué) ==" . PHP_EOL;
foreach ($bitacora->lineas() as $linea) {
    echo $linea . PHP_EOL;
}

echo PHP_EOL . "== RF6: trabajos cerrados por carlos en septiembre ==" . PHP_EOL;
echo implode(PHP_EOL, $bitacora->trabajosDe('carlos', $f('2026-09-01'), $f('2026-09-30 23:59'))) . PHP_EOL;
