<?php
declare(strict_types=1);

require_once __DIR__ . '/cargar.php';

$cliente = new Cliente(1, 'Ana Perez', '70000001');
$equipo = new Laptop(1, 'HP', 'Pavilion', 'DEMO-LAP-001', 'interna');

echo "FUSION OBSERVER + STRATEGY: avisos de estado y cobro por QR/efectivo.\n";

$datosQr = new DatosCobro('OT-FINAL-QR', 16500, 'QR-OT-FINAL-QR');
$cajaQr = new ServicioCaja(new CobroQR());
$ordenQr = new OrdenObservable(
    'OT-FINAL-QR',
    $cliente,
    $equipo,
    'No enciende',
    new DateTimeImmutable('2026-09-14')
);
$ordenQr->suscribir(new BitacoraOrden());
$ordenQr->suscribir(new AvisoEquipoListo($cajaQr, $datosQr));
$ordenQr->suscribir(new ProcesarCobroEnEntrega($cajaQr, $datosQr));
$ordenQr->aplicar('DIAGNOSTICO_LISTO');
$ordenQr->aplicar('CLIENTE_AUTORIZA');
$ordenQr->aplicar('REPARACION_TERMINADA');
$ordenQr->aplicar('EQUIPO_ENTREGADO');

$datosEfectivo = new DatosCobro('OT-FINAL-EF', 16500, null, 20000);
$cajaEfectivo = new ServicioCaja(new CobroEfectivo());
$ordenEfectivo = new OrdenObservable(
    'OT-FINAL-EF',
    $cliente,
    $equipo,
    'Falla de teclado',
    new DateTimeImmutable('2026-09-14')
);
$ordenEfectivo->suscribir(new BitacoraOrden());
$ordenEfectivo->suscribir(new AvisoEquipoListo($cajaEfectivo, $datosEfectivo));
$ordenEfectivo->suscribir(new ProcesarCobroEnEntrega($cajaEfectivo, $datosEfectivo));
$ordenEfectivo->aplicar('DIAGNOSTICO_LISTO');
$ordenEfectivo->aplicar('CLIENTE_AUTORIZA');
$ordenEfectivo->aplicar('REPARACION_TERMINADA');
$ordenEfectivo->aplicar('EQUIPO_ENTREGADO');
