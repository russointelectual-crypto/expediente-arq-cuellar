// Refactor: Richard Cuellar Rojas
// INTEGRADORA · VARIANTE A — Comedor Universitario "Sabor Andino"
//
// Violación curada: #3 DIP (Principio de Inversión de Dependencias).
//   Antes: GestorDePedidos hacía `new BaseDeDatosComedor()` y `new CorreoUniversitario()`
//          dentro de ProcesarPedido → la regla de negocio dependía de detalles concretos.
//   Ahora: GestorDePedidos depende SOLO de abstracciones (IRepositorioPedidos, INotificador)
//          que recibe por constructor. Los detalles concretos se eligen en la raíz de composición.
//
// #1 SRP y #2 OCP quedan SIN curar a propósito (marcadas abajo) — ver detecciones.md.
// El comportamiento observable es el mismo que el del esqueleto (refactor = misma salida).

using System;
using System.Collections.Generic;

namespace Integradora.Comedor;

// ─────────────────────────────────────────────────────────────────────────────
// Abstracciones: las define el módulo de ALTO nivel (lo que el gestor NECESITA),
// no la base de datos ni el correo. Por eso la dependencia queda "invertida".
// ─────────────────────────────────────────────────────────────────────────────

public interface IRepositorioPedidos
{
    void GuardarPedido(string estudiante, string tipoMenu, int cantidad, decimal total);
}

public interface INotificador
{
    void Enviar(string mensaje);
}

// ─────────────────────────────────────────────────────────────────────────────
// Módulo de alto nivel: ya no conoce ninguna clase concreta de infraestructura.
// ─────────────────────────────────────────────────────────────────────────────

public class GestorDePedidos
{
    private readonly IRepositorioPedidos _repositorio;
    private readonly INotificador _notificador;

    public GestorDePedidos(IRepositorioPedidos repositorio, INotificador notificador)
    {
        _repositorio = repositorio ?? throw new ArgumentNullException(nameof(repositorio));
        _notificador = notificador ?? throw new ArgumentNullException(nameof(notificador));
    }

    public void ProcesarPedido(string estudiante, string tipoMenu, int cantidad)
    {
        // [#2 OCP — pendiente a propósito] los precios siguen fijos en el switch.
        decimal precioBase;
        switch (tipoMenu)
        {
            case "estandar":
                precioBase = 12;
                break;
            case "vegetariano":
                precioBase = 14;
                break;
            case "beca":
                precioBase = 5;
                break;
            default:
                precioBase = 12;
                break;
        }
        decimal total = precioBase * cantidad;

        // DIP curado: antes `var baseDeDatos = new BaseDeDatosComedor();`
        _repositorio.GuardarPedido(estudiante, tipoMenu, cantidad, total);

        // [#1 SRP — pendiente a propósito] imprimir el vale sigue dentro del gestor.
        Console.WriteLine("----- VALE DE COMEDOR -----");
        Console.WriteLine($"{estudiante}: {cantidad} x menú {tipoMenu}");
        Console.WriteLine($"TOTAL: {total:0.00} Bs");

        // DIP curado: antes `var correo = new CorreoUniversitario();`
        _notificador.Enviar($"Pedido registrado: {cantidad} x {tipoMenu}, {estudiante}");
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Detalles de bajo nivel: ahora IMPLEMENTAN las abstracciones del alto nivel.
// ─────────────────────────────────────────────────────────────────────────────

public class BaseDeDatosComedor : IRepositorioPedidos
{
    public void GuardarPedido(string estudiante, string menu, int cantidad, decimal total)
        => Console.WriteLine($"[BD] INSERT INTO pedidos VALUES ('{estudiante}', '{menu}', {cantidad}, {total})");
}

public class CorreoUniversitario : INotificador
{
    public void Enviar(string mensaje) => Console.WriteLine($"[CORREO] {mensaje}");
}

// Prueba de que la cura sirve: otra implementación se enchufa SIN tocar GestorDePedidos.
public class RepositorioEnMemoria : IRepositorioPedidos
{
    public List<string> Guardados { get; } = new();

    public void GuardarPedido(string estudiante, string menu, int cantidad, decimal total)
        => Guardados.Add($"{estudiante} | {menu} | {cantidad} | {total:0.00} Bs");
}

// ─────────────────────────────────────────────────────────────────────────────
// Raíz de composición: el ÚNICO lugar que conoce las clases concretas.
// ─────────────────────────────────────────────────────────────────────────────

public static class Demo
{
    public static void Correr()
    {
        // 1) Producción: misma salida que el esqueleto original.
        var gestor = new GestorDePedidos(new BaseDeDatosComedor(), new CorreoUniversitario());
        gestor.ProcesarPedido("Noelia", "vegetariano", 2);

        // 2) Prueba: el MISMO gestor, sin base de datos real. Imposible en el esqueleto.
        Console.WriteLine();
        Console.WriteLine("--- Mismo GestorDePedidos con repositorio en memoria (prueba) ---");
        var memoria = new RepositorioEnMemoria();
        var gestorDePrueba = new GestorDePedidos(memoria, new CorreoUniversitario());
        gestorDePrueba.ProcesarPedido("Mateo", "beca", 1);
        Console.WriteLine($"Pedidos guardados en memoria: {memoria.Guardados.Count} -> {memoria.Guardados[0]}");
    }
}
