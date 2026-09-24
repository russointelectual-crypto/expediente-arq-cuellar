<?php
declare(strict_types=1);

/**
 * con-builder — la base + BUILDER para armar la orden de trabajo.
 * Ejecutar:  php h3/con-builder/demo.php
 */
foreach (['Cliente', 'Equipo', 'Laptop', 'PcEscritorio', 'OrdenDeTrabajo', 'OrdenDeTrabajoBuilder'] as $clase) {
    require_once __DIR__ . "/{$clase}.php";
}

$hoy  = new DateTimeImmutable('2026-09-12 15:20');
$luis = new Cliente('Luis Alberto Vargas Suárez', '72223344', '60011223');

echo "== ANTES (base): constructor posicional ==" . PHP_EOL;
echo "new OrdenDeTrabajo('R-000301', \$luis, \$equipo, ['BISAGRA_ROTA'], 'ana', \$hoy, '', [], ['foto.jpg'], [], false);" . PHP_EOL;
echo "¿Cuál de los [] eran las fotos y cuál los accesorios? Hay que contar parámetros." . PHP_EOL;

echo PHP_EOL . "== CON BUILDER: se lee como el informe técnico ==" . PHP_EOL;
$orden = OrdenDeTrabajoBuilder::nuevaOrden('R-000301')
    ->paraCliente($luis)
    ->conEquipo(new Laptop('Acer', 'Aspire 5 A515', 'Core i5-1235U', 8, 0, 512, Laptop::BATERIA_INTERNA))
    ->porMotivo('BISAGRA_ROTA')
    ->porMotivo('PANTALLA_DANADA')
    ->contadoPorElCliente('Se cayó; la pantalla tiene rayas y la bisagra izquierda está suelta')
    ->conObservacion('Faltan 2 pernos de la base')
    ->conObservacion('Esquina superior derecha golpeada')
    ->conFoto('fotos/R-000301-frente.jpg')
    ->conFoto('fotos/R-000301-base.jpg')
    ->dejaAccesorio('cargador original')
    ->registradaPor('ana', $hoy)
    ->build();

echo $orden->resumen() . PHP_EOL;
echo '   motivos:       ' . implode(', ', $orden->motivos) . PHP_EOL;
echo '   observaciones: ' . implode(' | ', $orden->observacionesFisicas) . PHP_EOL;
echo '   fotos:         ' . count($orden->fotos) . PHP_EOL;
echo '   accesorios:    ' . implode(', ', $orden->accesoriosDejados) . PHP_EOL;

echo PHP_EOL . "== Servicio pedido directamente: entra pre-autorizado ==" . PHP_EOL;
$upgrade = OrdenDeTrabajoBuilder::nuevaOrden('R-000302')
    ->paraCliente($luis)
    ->conEquipo(new PcEscritorio('Lenovo', 'ThinkCentre M70s', 'Core i5-10400', 8, 1000, 0))
    ->porMotivo('UPGRADE_SSD')->porMotivo('ACTUALIZAR_SO')
    ->conFoto('fotos/R-000302-1.jpg')
    ->preAutorizada()
    ->registradaPor('carlos', $hoy)       // un técnico también puede registrar equipos
    ->build();
echo $upgrade->resumen() . '  (pre-autorizada: ' . ($upgrade->preAutorizada ? 'sí' : 'no') . ')' . PHP_EOL;

echo PHP_EOL . "== El builder avisa TODO lo que falta de una vez (útil para el formulario) ==" . PHP_EOL;
try {
    OrdenDeTrabajoBuilder::nuevaOrden('R-000303')->paraCliente($luis)->build();
} catch (InvalidArgumentException $e) {
    echo "[rechazado] {$e->getMessage()}" . PHP_EOL;
}
try {
    OrdenDeTrabajoBuilder::nuevaOrden('R-000304')
        ->paraCliente($luis)
        ->conEquipo(new PcEscritorio('HP', 'EliteDesk', 'Core i7', 16, 0, 512))
        ->porMotivo('NO_ENCIENDE')->conFoto('f.jpg')->preAutorizada()
        ->registradaPor('ana', $hoy)->build();
} catch (InvalidArgumentException $e) {
    echo "[rechazado] {$e->getMessage()}" . PHP_EOL;
}
