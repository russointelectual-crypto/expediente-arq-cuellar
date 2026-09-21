# Decorator — recibos extensibles

`ReciboBasico` implementa `IRecibo`. `ConDetalleEquipo` y `ConRecomendaciones` envuelven ese contrato para agregar información opcional sin modificar la orden ni crear una clase por cada combinación.

La carpeta contiene una copia ejecutable de `h3/base/` para que la práctica sea aislada.

Ejecutar:

```bash
php H3/con-decorator/demo.php
```
