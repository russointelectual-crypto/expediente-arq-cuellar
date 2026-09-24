# H3 + H4 (parte A) — Laboratorio de patrones

**Estuante:** Richard Cuellar Rojas · **Variante 6:** Taller y soporte técnico · Código en **PHP 8.1+**, sin dependencias.

Cada carpeta `con-*/` es una **copia de `base/`** con **un solo** patrón encima (nunca uno sobre otro), para ver el efecto puro de cada uno. `final/` es la fusión de los dos que mi caso necesita.

| Carpeta | Patrón | Problema del taller que ataca | Veredicto |
|---|---|---|---|
| [`base/`](base/) | — | El corazón del caso: cliente, equipo, orden y su flujo de estados | Punto de partida |
| [`con-factory/`](con-factory/) | Factory Method | Crear `Laptop` o `PcEscritorio` según el formulario, sin `if` | Útil en Recepción; no entra en la fusión |
| [`con-builder/`](con-builder/) | Builder | Armar la orden (6 obligatorios + opcionales) sin un constructor de 11 parámetros | Útil en Recepción; no entra en la fusión |
| [`con-adapter/`](con-adapter/) | Adapter | Cobrar con la librería QR del banco, que no podemos modificar | Se queda en Caja; no entra en la fusión |
| [`con-singleton/`](con-singleton/singleton.md) | Singleton | — | **No lo pide el caso** (explicado en `singleton.md`) |
| [`con-observer/`](con-observer/) | Observer | Avisar a las secretarias y a la bitácora cuando una orden se cierra o vence su plazo | **Elegido para la fusión** |
| [`con-strategy/`](con-strategy/) | Strategy | Cobrar distinto según cómo terminó la orden | **Elegido para la fusión** |
| [`con-decorator/`](con-decorator/) | Decorator | Apilar licencias y extras sobre la mano de obra | Descartado: mi problema es sumar ítems, no apilar comportamiento |
| [`final/`](final/) | **Observer + Strategy** | Cierre de la orden: avisar a quién llamar **y** cuánto cobrar | **El corazón de la defensa** |

## Correr todo

```bash
for d in base con-factory con-builder con-adapter con-observer con-strategy con-decorator final; do
  echo "--- $d"; php h3/$d/demo.php > /dev/null && echo "corre OK"
done
php h3/final/pruebas.php
```

## Qué cambió en `OrdenDeTrabajo` en cada copia

| Copia | Cambio en la clase de la base |
|---|---|
| factory, builder, adapter, decorator | ninguno |
| observer | `suscribir()`, `desuscribir()`, `revisarPlazo()`; cada transición publica un `EventoOrden` **después** de completar los datos |
| strategy | `totalACobrar()` (if/else) se reemplazó por `desenlace()` |
| final | los dos cambios anteriores juntos |
