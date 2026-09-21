# Fusión final: Observer + Strategy

Esta carpeta integra los dos patrones que mejor responden al dominio del taller y soporte técnico:

- `Observer`: `OrdenObservable` publica cada cambio válido; `BitacoraOrden`, `AvisoEquipoListo` y `ProcesarCobroEnEntrega` reaccionan sin acoplarse entre sí.
- `Strategy`: `ServicioCaja` cambia entre `CobroQR` y `CobroEfectivo` sin modificar los observadores ni la orden.

La convivencia se ve en el flujo de la demo: el cambio a `lista` activa un aviso que usa la estrategia de cobro seleccionada; el cambio a `entregada` activa el cobro correspondiente.

La carpeta incluye una copia de la base y todos los archivos necesarios para ejecutarse de forma aislada.

```bash
php H3/final/demo.php
```
