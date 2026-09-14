<?php
declare(strict_types=1);

require_once __DIR__ . '/OrdenDeTrabajoBuilder.php';

$cliente = new Cliente(1, 'Ana Perez', '70000001');
$equipo = new Laptop(1, 'HP', 'Pavilion', 'DEMO-LAP-001', 'interna');
$orden = (new OrdenDeTrabajoBuilder())
    ->conRecibo('OT-B-001')
    ->paraCliente($cliente)
    ->conEquipo($equipo)
    ->porMotivo('No enciende')
    ->ingresadaEl(new DateTimeImmutable('2026-09-14'))
    ->comprometidaPara(new DateTimeImmutable('2026-09-17'))
    ->construir();

echo "BUILDER\n";
echo $orden->numeroRecibo . ' | ' . $orden->equipo->descripcion() . "\n";
echo 'Estado: ' . $orden->estadoActual() . "\n";
echo 'Compromiso: ' . $orden->fechaCompromiso->format('Y-m-d') . "\n";
