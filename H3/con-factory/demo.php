<?php
declare(strict_types=1);

require_once __DIR__ . '/RecepcionLaptop.php';
require_once __DIR__ . '/RecepcionPCEscritorio.php';

$cliente = new Cliente(1, 'Ana Perez', '70000001');
$recepciones = [
    new RecepcionLaptop(1, 'HP', 'Pavilion', 'DEMO-LAP-001', 'interna'),
    new RecepcionPCEscritorio(2, 'Dell', 'OptiPlex', 'DEMO-PC-002', 'torre', true)
];
echo "FACTORY METHOD\n";
foreach ($recepciones as $indice => $recepcion) {
    $orden = $recepcion->registrarIngreso('OT-F-' . ($indice + 1), $cliente,
        'No enciende', new DateTimeImmutable('2026-09-14'));
    echo $orden->numeroRecibo . ' | ' . get_class($orden->equipo) . "\n";
    echo $orden->equipo->revisionDeIngreso() . "\n";
}
