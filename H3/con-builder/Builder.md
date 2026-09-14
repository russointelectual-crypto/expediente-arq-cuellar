# Builder - Construccion de la orden

OrdenDeTrabajoBuilder recibe por pasos el recibo, cliente, equipo, motivo, fecha de ingreso y compromiso opcional. construir() comprueba que esten los datos requeridos y llama al constructor de la misma OrdenDeTrabajo de base. Las validaciones del dominio siguen en la orden y tambien protegen la creacion directa.

El producto es OrdenDeTrabajo; el constructor concreto es OrdenDeTrabajoBuilder. demo.php dirige los pasos. No se agrega una clase Director porque no hay recetas complejas reutilizables que la justifiquen; esta es la variante fluida de Builder, con un unico producto.

El builder no publica una orden incompleta. Tras una construccion correcta se reinicia: no arrastra recibos o fechas al siguiente producto. Si falla, conserva lo introducido para permitir corregirlo. Cada construir() exitoso devuelve una nueva orden; las referencias a cliente y equipo se comparten intencionalmente porque representan entidades existentes.

Ventaja: llamadas legibles en recepcion y una fecha opcional. Costo: mas codigo que usar directamente el constructor; aqui se implementa para comparar el patron y su posible utilidad al crecer la ficha de ingreso.

Las cinco clases de base son identicas. El equipo se crea directamente, sin Factory Method.

Ejecutar: `php h3/con-builder/demo.php`.
