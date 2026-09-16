# Observer — avisos sobre la orden de trabajo

## Aplicacion a mi variante

En el taller, cuando una orden cambia de estado, distintos interesados necesitan enterarse. La bitacora registra el cambio y el aviso al cliente reacciona cuando el equipo queda listo. Esto desarrolla el caso de notificaciones previsto en H1/H2 y mencionado por el docente como RF5.

El ejemplo de clase en C# usa SecretariaDeAsistencia e IInteresadoEnFaltas. Aqui el sujeto es OrdenObservable y el contrato es IObservadorOrden, implementados en PHP como el resto del laboratorio.

| Pieza | Funcion |
| --- | --- |
| IObservadorOrden | Contrato actualizar(CambioEstado). |
| CambioEstado | Datos del cambio: recibo, cliente, celular y estados. |
| OrdenObservable | Sujeto: suscribe, retira suscriptores y publica cambios validos. |
| AvisoEquipoListo | Observador que simula un aviso al cliente cuando el estado es lista. |
| BitacoraOrden | Observador que registra todos los cambios recibidos en memoria. |
| demo.php | Conecta los objetos y recorre el ciclo de una orden. |

OrdenObservable hereda de la OrdenDeTrabajo existente para reutilizar su constructor y sus validaciones. Sobrescribe aplicar(), llama primero a parent::aplicar() y publica solamente si esa operacion termina correctamente. No instancia ni nombra observadores concretos: las suscripciones se realizan en demo.php.

## Ejecutar desde la raiz

```bash
php H3/con-observer/demo.php
```

