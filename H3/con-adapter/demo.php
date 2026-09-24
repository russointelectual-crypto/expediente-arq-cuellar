<?php
declare(strict_types=1);

/**
 * con-adapter — la base + ADAPTER para cobrar con el QR del banco.
 * Ejecutar:  php h3/con-adapter/demo.php
 */
foreach (['Cliente', 'Equipo', 'Laptop', 'PcEscritorio', 'OrdenDeTrabajo',
          'MetodoDePago', 'ComprobanteDePago', 'PagoEnEfectivo', 'AdaptadorPagoQr'] as $clase) {
    require_once __DIR__ . "/{$clase}.php";
}
require_once __DIR__ . '/externo/SdkQrBanco.php';

$f = fn (string $s) => new DateTimeImmutable($s);

/** Deja una orden lista para entregar (lo mismo que hace la base). */
function ordenSolucionada(string $recibo, Cliente $cliente, float $manoDeObra, array $repuestos, callable $f): OrdenDeTrabajo
{
    $o = new OrdenDeTrabajo($recibo, $cliente, new Laptop('HP', 'Pavilion 14', 'Core i7-1165G7', 16, 0, 512, Laptop::BATERIA_INTERNA),
        ['PANTALLA_DANADA'], 'ana', $f('2026-09-15 09:00'), '', [], ["fotos/{$recibo}-1.jpg"]);
    $o->tomar('miguel');
    $o->registrarDiagnostico('miguel', 'Panel LCD roto', OrdenDeTrabajo::TRABAJO_ELECTRONICA, $manoDeObra + array_sum(array_column($repuestos, 'precio')));
    $o->registrarRespuestaCliente('miguel', true);
    $o->registrarSolucion('miguel', ['Cambio de pantalla'], $repuestos, $manoDeObra, 30, $f('2026-09-16 17:00'));
    return $o;
}

/** Caja solo conoce la interfaz MetodoDePago: no sabe si detrás hay efectivo o un banco. */
function cobrarYEntregar(OrdenDeTrabajo $orden, MetodoDePago $metodo, string $usuario): void
{
    $comprobante = $metodo->cobrar($orden->totalACobrar(), "Orden {$orden->numeroRecibo}");
    $orden->entregar($usuario, OrdenDeTrabajo::CON_RECIBO);
    echo "  {$comprobante}" . PHP_EOL . "  → {$orden->numeroRecibo} {$orden->estado()} (cobró {$usuario})" . PHP_EOL;
}

$banco = new SdkQrBanco(apiKey: 'CLAVE-DE-PRUEBA', cuentaDestino: '1000-123456-7');
$pantalla = [['descripcion' => 'Pantalla 14" FHD IPS 30 pines', 'precio' => 520.0]];

echo "== 1) Cobro por QR a través del adaptador ==" . PHP_EOL;
$o1 = ordenSolucionada('R-000401', new Cliente('Carla Patricia Soliz Mendoza', '77788899'), 150.0, $pantalla, $f);
cobrarYEntregar($o1, new AdaptadorPagoQr($banco), 'ana');

echo PHP_EOL . "== 2) Cobro en efectivo: misma interfaz, mismo código de Caja ==" . PHP_EOL;
$o2 = ordenSolucionada('R-000402', new Cliente('Jorge Luis Céspedes Añez', '68877665'), 150.0, $pantalla, $f);
cobrarYEntregar($o2, new PagoEnEfectivo(700.0), 'gerente');

echo PHP_EOL . "== 3) El cliente nunca paga el QR: no se entrega el equipo ==" . PHP_EOL;
$o3 = ordenSolucionada('R-000403', new Cliente('Pedro Pablo Flores Arce', '71112233'), 150.0, $pantalla, $f);
try {
    cobrarYEntregar($o3, new AdaptadorPagoQr(new SdkQrBanco('CLAVE-DE-PRUEBA', '1000-123456-7', simularQueNadiePaga: true)), 'ana');
} catch (RuntimeException $e) {
    echo "  [no entregado] {$e->getMessage()}" . PHP_EOL . "  → {$o3->numeroRecibo} sigue {$o3->estado()}" . PHP_EOL;
}

echo PHP_EOL . "== Por qué hace falta el adaptador: así habla el banco ==" . PHP_EOL;
$crudo = $banco->generarQR(67000, 'BOB', 'Orden R-000401');
echo '  $banco->generarQR(67000, "BOB", ...) → ' . json_encode(array_diff_key($crudo, ['qr_base64' => 1])) . PHP_EOL;
echo '  $banco->consultarEstado(...)          → ' . $banco->consultarEstado($crudo['id_transaccion']) . '  (0 = pendiente, 1 = pagado, 2 = expirado)' . PHP_EOL;
