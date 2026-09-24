<?php
declare(strict_types=1);

/**
 * con-strategy — la base + STRATEGY: cuánto se cobra depende de cómo terminó la orden.
 * Ejecutar:  php h3/con-strategy/demo.php
 */
foreach (['Cliente', 'Equipo', 'Laptop', 'PcEscritorio', 'OrdenDeTrabajo',
          'Cobro', 'ReglaDeCobro', 'CobroPorReparacion', 'CobroPorDiagnostico', 'SinCargo', 'CalculadoraDeCobro'] as $clase) {
    require_once __DIR__ . "/{$clase}.php";
}

$f = fn (string $s) => new DateTimeImmutable($s);

// Punto de composición: la política de cobro vigente del taller (la fija el gerente).
$calculadora = new CalculadoraDeCobro([
    OrdenDeTrabajo::SOLUCIONADA            => new CobroPorReparacion(),
    OrdenDeTrabajo::CLIENTE_NO_AUTORIZO    => new CobroPorDiagnostico(),
    OrdenDeTrabajo::SIN_REPARACION_POSIBLE => new SinCargo(),
]);

function nuevaOrden(string $recibo, string $motivo, callable $f): OrdenDeTrabajo
{
    return new OrdenDeTrabajo($recibo, new Cliente('Cliente de Prueba', '70000001'),
        new Laptop('Dell', 'Inspiron 15 3520', 'Core i5-1235U', 8, 0, 512, Laptop::BATERIA_INTERNA),
        [$motivo], 'ana', $f('2026-09-18 09:00'), '', [], ["fotos/{$recibo}.jpg"]);
}

// 1) Se reparó
$reparada = nuevaOrden('R-000601', 'NO_ENCIENDE', $f);
$reparada->tomar('carlos');
$reparada->registrarDiagnostico('carlos', 'Mosfet en corto', OrdenDeTrabajo::TRABAJO_ELECTRONICA, 300.0, 50.0);
$reparada->registrarRespuestaCliente('carlos', true);
$reparada->registrarSolucion('carlos', ['Cambio de mosfet', 'Limpieza'],
    [['descripcion' => 'Mosfet AON6414', 'precio' => 35.0], ['descripcion' => 'Pasta térmica', 'precio' => 25.0]], 240.0, 30);

// 2) Falla electrónica, el cliente no autorizó → se cobra diagnóstico
$rechazoElectronica = nuevaOrden('R-000602', 'INGRESO_LIQUIDO', $f);
$rechazoElectronica->tomar('miguel');
$rechazoElectronica->registrarDiagnostico('miguel', 'Placa sulfatada', OrdenDeTrabajo::TRABAJO_ELECTRONICA, 520.0, 50.0);
$rechazoElectronica->registrarRespuestaCliente('miguel', false);

// 3) Falla de software, el cliente no autorizó → misma regla, pero no cobra (RN7)
$rechazoSoftware = nuevaOrden('R-000603', 'FALLA_SOFTWARE', $f);
$rechazoSoftware->tomar('miguel');
$rechazoSoftware->registrarDiagnostico('miguel', 'Windows dañado, requiere reinstalación', OrdenDeTrabajo::TRABAJO_SOFTWARE, 150.0, 30.0);
$rechazoSoftware->registrarRespuestaCliente('miguel', false);

// 4) No tiene reparación posible
$irreparable = nuevaOrden('R-000604', 'NO_ENCIENDE', $f);
$irreparable->tomar('carlos');
$irreparable->registrarDiagnostico('carlos', 'Chip de video dañado, no hay repuesto', OrdenDeTrabajo::TRABAJO_ELECTRONICA, 0.0, 50.0);
$irreparable->registrarRespuestaCliente('carlos', true);
$irreparable->cerrarSinSolucion('carlos', ['Reballing de prueba sin éxito']);

$ordenes = [$reparada, $rechazoElectronica, $rechazoSoftware, $irreparable];

$mostrar = function (string $titulo) use ($ordenes, $calculadora): void {
    echo PHP_EOL . "== {$titulo} ==" . PHP_EOL;
    foreach ($ordenes as $o) {
        $cobro = $calculadora->cobroDe($o);
        $regla = $cobro->regla . str_repeat(' ', max(0, 17 - preg_match_all('/./u', $cobro->regla)));
        printf("%s  %-22s → %s Bs %7.2f   %s%s",
            $o->numeroRecibo, $o->desenlace(), $regla, $cobro->total(), $cobro->detalle(), PHP_EOL);
    }
};

$mostrar('Política vigente');

// El gerente decide: "desde octubre, si no hay reparación posible también se cobra el diagnóstico electrónico".
// Se cambia UNA entrada de la tabla. OrdenDeTrabajo y las otras reglas no se tocan.
$calculadora->usarRegla(OrdenDeTrabajo::SIN_REPARACION_POSIBLE, new CobroPorDiagnostico());
$mostrar('Nueva política del gerente (solo cambia R-000604)');

echo PHP_EOL . "== Una orden en curso no se puede cobrar ==" . PHP_EOL;
try {
    $calculadora->cobroDe(nuevaOrden('R-000605', 'TECLADO', $f));
} catch (DomainException $e) {
    echo "[rechazado] {$e->getMessage()}" . PHP_EOL;
}
