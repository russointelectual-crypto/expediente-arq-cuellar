# Decorator: agregados combinables del recibo

## Respuesta a la consigna

En mi variante, Orden de Trabajo de un taller de soporte tecnico, el recibo puede llevar agregados que se combinan: el detalle del equipo y las recomendaciones de cuidado. Se agregan al texto del recibo sin cambiar OrdenDeTrabajo ni crear una subclase por cada combinacion.

| Elemento del patron | Archivo | Responsabilidad |
| --- | --- | --- |
| Contrato | IRecibo.php | Define generar(): string. |
| Pieza base | ReciboBasico.php | Genera numero de recibo, cliente, motivo y estado. |
| Decorador abstracto | ReciboDecorador.php | Guarda otro IRecibo y delega generar(). |
| Capa 1 | ConDetalleEquipo.php | Agrega descripcion y revision de ingreso del equipo. |
| Capa 2 | ConRecomendaciones.php | Agrega recomendaciones recibidas en el constructor. |
| Cliente | demo.php | Arma dos combinaciones directamente en las llamadas. |

Se reutilizan las clases existentes de ../base/ mediante require_once. No se duplican ni modifican. Esta practica funciona de manera independiente de las demos de otros patrones.



Ejecutar: `H3/con-decorator/demo.php`




## Las dos combinaciones

```php
// Base + una capa.
new ConDetalleEquipo($base, $orden->equipo);

// Base + las dos capas.
new ConRecomendaciones(
    new ConDetalleEquipo($base, $orden->equipo),
    'Evitar liquidos y mantener libres las rejillas de ventilacion.'
);
```

Cada capa recibe IRecibo, por eso puede envolver tanto ReciboBasico como otra capa. Primero genera el contenido recibido y luego agrega su propio texto. Las dos combinaciones usan las mismas clases; no existe una clase ReciboConDetalleYRecomendaciones.

Tambien se puede usar ConRecomendaciones directamente sobre la base, o invertir las capas. Al invertirlas cambia el orden del texto agregado. La demo solo muestra las dos combinaciones solicitadas.


