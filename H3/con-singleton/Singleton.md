# Decision sobre Singleton

No aplico Singleton en este H3 del taller de soporte tecnico.

Cada cliente, equipo y orden tiene identidad propia: pueden existir muchos al mismo tiempo. Hacer global alguna de estas entidades mezclaria datos de clientes y perjudicaria la fiabilidad.

Tampoco necesito garantizar una sola instancia global del notificador. En Adapter creo el proveedor y lo paso por el constructor. Asi puedo reemplazarlo en pruebas, y una instancia compartida por el programa no exige el patron Singleton.

Un Singleton de conexion a base de datos tampoco se justifica: este laboratorio trabaja en memoria y no tiene base de datos. Ademas, en una aplicacion PHP tradicional una instancia estatica no garantiza una unica conexion entre todos los procesos o solicitudes, ni resuelve concurrencia.

Reconsideraria la decision solo ante un recurso con una restriccion real de instancia unica dentro del mismo proceso, despues de evaluar su ciclo de vida y las alternativas. Por ahora, constructores e inyeccion explicita son suficientes.

Esta carpeta contiene la justificacion permitida por la consigna; no incorpora una cuarta variante de codigo innecesaria.
