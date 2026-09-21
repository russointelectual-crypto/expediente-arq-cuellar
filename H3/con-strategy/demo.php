<?php
declare(strict_types=1);

require_once __DIR__ . '/cargar.php';

$datosQr = new DatosCobro('OT-QR-001', 16500, 'QR-OT-QR-001');
$datosEfectivo = new DatosCobro('OT-EF-001', 16500, null, 20000);
$caja = new ServicioCaja(new CobroQR());

echo "STRATEGY: la caja cambia el medio de cobro sin cambiar su cliente.\n";
echo $caja->preparar($datosQr) . "\n";
echo $caja->cobrar($datosQr) . "\n";

$caja->cambiarPolitica(new CobroEfectivo());
echo $caja->preparar($datosEfectivo) . "\n";
echo $caja->cobrar($datosEfectivo) . "\n";
