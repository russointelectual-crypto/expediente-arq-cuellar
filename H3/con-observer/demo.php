<?php
declare(strict_types=1);

require_once __DIR__ . '/cargar.php';

$cliente = new Cliente(1, 'Ana Perez', '70000001');
$equipo = new Laptop(1, 'HP', 'Pavilion', 'DEMO-LAP-001', 'interna');
$orden = new OrdenObservable('OT-001', $cliente, $equipo, 'No enciende',
    new DateTimeImmutable('2026-09-14'));

$aviso = new AvisoEquipoListo();
$bitacora = new BitacoraOrden();
$orden->suscribir($aviso);
$orden->suscribir($bitacora);

echo "OBSERVER: el sujeto publica y los suscriptores reaccionan.\n";
$orden->aplicar('DIAGNOSTICO_LISTO');
$orden->aplicar('CLIENTE_AUTORIZA');
$orden->aplicar('REPARACION_TERMINADA');

echo "-- Se retira el aviso; la bitacora sigue suscrita. --\n";
$orden->desuscribir($aviso);
$orden->aplicar('EQUIPO_ENTREGADO');
echo 'Cambios registrados: ' . count($bitacora->registros()) . "\n";
