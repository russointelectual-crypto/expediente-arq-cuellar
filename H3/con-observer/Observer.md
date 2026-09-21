# Observer — avisos y bitácora de órdenes

El sujeto `OrdenObservable` publica un `CambioEstado` después de validar cada transición de la orden. `AvisoEquipoListo` y `BitacoraOrden` observan el mismo contrato, por lo que es posible agregar otro receptor sin modificar la orden.

La carpeta contiene una copia ejecutable de `h3/base/` para que la práctica sea aislada.

Ejecutar:

```bash
php H3/con-observer/demo.php
```
