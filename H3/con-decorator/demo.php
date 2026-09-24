<?php
declare(strict_types=1);

/**
 * con-decorator — la base + DECORATOR: apilar agregados (licencias, antivirus, repuestos) sobre la mano de obra.
 * Ejecutar:  php h3/con-decorator/demo.php
 */
foreach (['Cliente', 'Equipo', 'Laptop', 'PcEscritorio', 'OrdenDeTrabajo',
          'ServicioCobrable', 'ManoDeObra', 'AgregadoDeServicio', 'ConLicenciaWindows', 'ConLicenciaOffice',
          'ConAntivirus', 'ConClonacionDeDisco', 'ConRepuesto', 'ConRebaja'] as $clase) {
    require_once __DIR__ . "/{$clase}.php";
}

$f = fn (string $s) => new DateTimeImmutable($s);

echo "== 1) El técnico arma el servicio apilando agregados ==" . PHP_EOL;
$servicio = new ConAntivirus(
    new ConLicenciaOffice(
        new ConLicenciaWindows(
            new ConRepuesto(
                new ManoDeObra('Reinstalación de Windows y cambio a SSD', 120.0),
                'SSD SATA 480 GB', 280.0))));

echo 'Descripción: ' . $servicio->descripcion() . PHP_EOL;
printf('Total:       Bs %.2f%s', $servicio->precio(), PHP_EOL);

echo PHP_EOL . "== 2) Para registrar la solución en la orden hay que DESARMAR la cadena ==" . PHP_EOL;
$orden = new OrdenDeTrabajo('R-000701', new Cliente('Ana Lucía Torrez Vaca', '75556677'),
    new PcEscritorio('Lenovo', 'ThinkCentre M720', 'Core i5-8400', 8, 1000, 0),
    ['ACTUALIZAR_SO', 'UPGRADE_SSD'], 'ana', $f('2026-09-19 10:00'), '', [], ['fotos/R-000701.jpg'], [], true);
$orden->tomar('carlos');
$orden->registrarDiagnostico('carlos', 'HDD lento, Windows sin licencia', OrdenDeTrabajo::TRABAJO_ACTUALIZACION, $servicio->precio());

$lineas     = $servicio->lineas();                                    // la cadena, aplanada en una lista
$repuestos  = array_values(array_filter($lineas, fn ($l) => $l['esRepuesto']));
$otros      = array_values(array_filter($lineas, fn ($l) => !$l['esRepuesto']));
$orden->registrarSolucion('carlos',
    array_column($otros, 'concepto'),
    array_map(fn ($l) => ['descripcion' => $l['concepto'], 'precio' => $l['monto']], $repuestos),
    array_sum(array_column($otros, 'monto')),                         // licencias y antivirus terminan sumados como "mano de obra"
    30);
foreach ($lineas as $l) {
    $concepto = $l['concepto'] . str_repeat(' ', max(0, 58 - preg_match_all('/./u', $l['concepto'])));
    printf('   %s Bs %7.2f %s%s', $concepto, $l['monto'], $l['esRepuesto'] ? '(repuesto → inventario)' : '', PHP_EOL);
}
printf('La orden cobra Bs %.2f — coincide, pero licencias y antivirus quedaron sumados como "mano de obra".%s', $orden->totalACobrar(), PHP_EOL);

echo PHP_EOL . "== 3) Con la rebaja, el ORDEN de apilamiento cambia el precio ==" . PHP_EOL;
$base = fn () => new ConRepuesto(new ManoDeObra('Cambio a SSD', 80.0), 'SSD SATA 480 GB', 280.0);
$rebajaAlFinal  = new ConLicenciaWindows(new ConRebaja($base(), 10, 'gerente'));
$rebajaSobreTodo = new ConRebaja(new ConLicenciaWindows($base()), 10, 'gerente');
printf('   rebaja aplicada antes de la licencia:  Bs %.2f%s', $rebajaAlFinal->precio(), PHP_EOL);
printf('   rebaja aplicada sobre todo:            Bs %.2f%s', $rebajaSobreTodo->precio(), PHP_EOL);
echo '   Mismos agregados, distinto total: el cajero tendría que saber en qué orden se envolvió.' . PHP_EOL;
