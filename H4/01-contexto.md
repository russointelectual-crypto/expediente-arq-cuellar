# C4 — Nivel 1: contexto del sistema

**Caso:** Órdenes de Trabajo — Taller y soporte técnico.

Esta vista representa el alcance del sistema del H1, no una afirmación de que todo esté programado. Los cuatro actores provienen del inventario original. WhatsApp es una integración propuesta, coherente con el laboratorio Adapter; allí la API también es simulada. El cliente se comunica con el taller y recibe avisos: no se supone un portal de autoservicio.

```mermaid
flowchart TB
    cliente["Cliente · Persona"]
    secretaria["Secretaria / Operador · Persona"]
    tecnico["Técnico · Persona"]
    gerente["Gerente / Jefe de taller · Persona"]
    sistema["Sistema de Órdenes de Trabajo · Software del taller"]
    whatsapp["Proveedor de WhatsApp · Sistema externo propuesto"]
    cliente -->|Entrega datos y equipo presencialmente| secretaria
    secretaria -->|Registra clientes, recibe equipos, cobra y entrega| sistema
    tecnico -->|Registra diagnóstico, solución y estados| sistema
    gerente -->|Administra personal y consulta inventario y reportes| sistema
    sistema -->|Solicita avisos de equipo listo| whatsapp
    whatsapp -->|Entrega mensajes| cliente
```


