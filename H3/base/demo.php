<?php
declare(strict_types=1);

require_once __DIR__ . '/cargar.php';

$cliente = new Cliente(1, 'Ana Perez', '70000001');
$equipo = new Laptop(1, 'HP', 'Pavilion', 'DEMO-LAP-001', 'interna');
$orden = new OrdenDeTrabajo('OT-001', $cliente, $equipo, 'No enciende', new DateTimeImmutable('2026-09-14'));

echo "BASE: construccion directa, sin patrones.\n";
echo $orden->equipo->descripcion() . "\n";
echo $orden->equipo->revisionDeIngreso() . "\n";
echo 'Recibo: ' . $orden->numeroRecibo . ' | Estado: ' . $orden->estadoActual() . "\n";
$orden->aplicar('DIAGNOSTICO_LISTO');
echo 'Despues del diagnostico: ' . $orden->estadoActual() . "\n";
