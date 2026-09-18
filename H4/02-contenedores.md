# C4 — Nivel 2: contenedores

## Arquitectura objetivo propuesta

Un contenedor C4 es una aplicación o almacén de datos; no es una clase PHP ni necesariamente un contenedor Docker. Propongo una aplicación web monolítica para el tamaño del taller. La interfaz HTML es servida por PHP, por lo que no se presenta como una SPA desplegada por separado. MySQL y la API real de WhatsApp quedan pendientes de implementación.

```mermaid
flowchart TB
    personal["Secretaria, técnico y gerente · Personas"]
    cliente["Cliente · Persona"]
    subgraph sistema["Sistema de Órdenes de Trabajo"]
        app["Aplicación web · PHP 8 y HTML · Lógica, vistas, cobros y avisos"]
        bd[("Base de datos · MySQL propuesto · Órdenes, clientes, inventario y caja")]
    end
    whatsapp["API de WhatsApp · Sistema externo propuesto"]
    personal -->|HTTPS: operar y consultar| app
    app -->|SQL: guardar y consultar| bd
    app -->|HTTPS: solicitar aviso| whatsapp
    whatsapp -->|Mensaje al celular| cliente
```

