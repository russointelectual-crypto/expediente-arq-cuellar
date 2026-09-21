<?php
declare(strict_types=1);

require_once __DIR__ . '/cargar.php';

$cliente = new Cliente(1, 'Ana Perez', '70000001');
$equipo = new Laptop(1, 'HP', 'Pavilion', 'DEMO-LAP-001', 'interna');
$orden = new OrdenObservable(
    'OT-OBS-001',
    $cliente,
    $equipo,
    'No enciende',
    new DateTimeImmutable('2026-09-14')
);
$aviso = new AvisoEquipoListo();
$bitacora = new BitacoraOrden();
$orden->suscribir($aviso);
$orden->suscribir($bitacora);

echo "OBSERVER: la orden publica cambios a sus suscriptores.\n";
$orden->aplicar('DIAGNOSTICO_LISTO');
$orden->aplicar('CLIENTE_AUTORIZA');
$orden->aplicar('REPARACION_TERMINADA');

$orden->desuscribir($aviso);
$orden->aplicar('EQUIPO_ENTREGADO');
echo 'Registros conservados: ' . count($bitacora->registros()) . "\n";
