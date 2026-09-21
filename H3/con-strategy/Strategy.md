# Strategy — cobro por QR o en efectivo

`ServicioCaja` mantiene el contrato `IPoliticaCobro` y permite cambiar la política en tiempo de ejecución. `CobroQR` prepara una referencia de pago y `CobroEfectivo` valida el monto recibido y calcula el cambio.

La variación del medio de cobro queda encapsulada: la caja no necesita condicionales para conocer cómo se procesa cada pago.

La carpeta contiene una copia ejecutable de `h3/base/` para que la práctica sea aislada.

Ejecutar:

```bash
php H3/con-strategy/demo.php
```
