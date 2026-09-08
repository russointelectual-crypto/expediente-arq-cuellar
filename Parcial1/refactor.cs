// PARCIAL 1 · VARIANTE A — Farmacia "San Rafael"
// P1.2 — Cura DOS violaciones SOLID de las 4 detectadas en detecciones.md:
//   Cura 1: S (Single Responsibility) sobre GestorDePedidos.ProcesarPedido
//   Cura 2: D (Dependency Inversion) sobre GestorDePedidos.ProcesarPedido
// Las otras dos violaciones (O y L/I, sobre el switch de tipoCliente y sobre
// IEmpleadoDeFarmacia sobre Cajero) NO se tocan acá: quedan referenciadas tal
// como están en examen-p1-A.cs, según lo que pide la consigna.
//
// Este archivo reemplaza, dentro de Parcial1.Farmacia, a las clases
// GestorDePedidos, BaseDeDatosMySql y CorreoSmtp del examen original.
// Se declara en un namespace separado (.Refactor) solo para que ambos
// archivos puedan convivir sin chocar nombres de clase al compilar juntos;
// en el repositorio final estas clases reemplazan a las del original.

namespace Parcial1.Farmacia.Refactor;

// ---------------------------------------------------------------------------
// Refactor: Richard Cuellar Rojas
// ---------------------------------------------------------------------------

// Reemplaza el "todo junto en variables sueltas" del método original: agrupa
// los datos de un pedido ya procesado (con su descuento ya calculado).
public class Pedido
{
    public string Cliente { get; }
    public string TipoCliente { get; }
    public string Medicamento { get; }
    public int Cantidad { get; }
    public decimal PrecioUnitario { get; }
    public decimal Descuento { get; }
    public decimal TotalFinal => (Cantidad * PrecioUnitario) - Descuento;

    public Pedido(string cliente, string tipoCliente, string medicamento, int cantidad, decimal precioUnitario, decimal descuento)
    {
        Cliente = cliente;
        TipoCliente = tipoCliente;
        Medicamento = medicamento;
        Cantidad = cantidad;
        PrecioUnitario = precioUnitario;
        Descuento = descuento;
    }
}

// --- Cura 1: SRP -------------------------------------------------------
// Antes, GestorDePedidos.ProcesarPedido calculaba descuento, guardaba en la
// BD, armaba el comprobante Y enviaba el correo: cuatro razones de cambio en
// un solo método. Cada una de esas razones ahora vive en su propia clase.

// Única responsabilidad: saber calcular el descuento según el tipo de cliente.
public class CalculadoraDescuento
{
    public decimal CalcularDescuento(string tipoCliente, decimal total)
    {
        return tipoCliente switch
        {
            "particular" => 0,
            "asegurado" => total * 0.20m,
            "convenio" => total * 0.10m,
            _ => 0
        };
    }
}

// Única responsabilidad: dar formato y mostrar el comprobante.
public class GeneradorComprobante
{
    public void Generar(Pedido pedido)
    {
        Console.WriteLine("----- COMPROBANTE -----");
        Console.WriteLine($"{pedido.Cantidad} x {pedido.Medicamento}");
        Console.WriteLine($"Cliente: {pedido.Cliente} ({pedido.TipoCliente})");
        Console.WriteLine($"TOTAL: {pedido.TotalFinal:0.00} Bs");
    }
}

// --- Cura 2: DIP ---------------------------------------------------------
// Antes, GestorDePedidos (política de alto nivel) instanciaba directamente
// BaseDeDatosMySql y CorreoSmtp (detalles de bajo nivel). Ahora depende de
// estas dos abstracciones, que él mismo define, y recibe las implementaciones
// concretas por constructor. El detalle concreto pasa a implementar el
// contrato, no al revés.

public interface IRepositorioPedidos
{
    void GuardarPedido(Pedido pedido);
}

public interface INotificador
{
    void Notificar(string destinatario, string mensaje);
}

// Adaptador concreto de bajo nivel para el repositorio (antes BaseDeDatosMySql).
public class RepositorioPedidosMySql : IRepositorioPedidos
{
    public void GuardarPedido(Pedido pedido)
        => Console.WriteLine($"[MYSQL] INSERT INTO pedidos VALUES ('{pedido.Cliente}', '{pedido.Medicamento}', {pedido.Cantidad}, {pedido.TotalFinal})");
}

// Adaptador concreto de bajo nivel para la notificación (antes CorreoSmtp).
public class NotificadorSmtp : INotificador
{
    public void Notificar(string destinatario, string mensaje)
        => Console.WriteLine($"[SMTP] {mensaje}");
}

// GestorDePedidos ya no calcula, no imprime, no guarda ni notifica "a mano":
// orquesta cuatro colaboradores inyectados por constructor. Cambiar el motor
// de base de datos, el canal de notificación, o probar la clase con dobles de
// prueba ya no requiere tocar esta clase.
public class GestorDePedidos
{
    private readonly CalculadoraDescuento _calculadora;
    private readonly IRepositorioPedidos _repositorio;
    private readonly GeneradorComprobante _generadorComprobante;
    private readonly INotificador _notificador;

    public GestorDePedidos(
        CalculadoraDescuento calculadora,
        IRepositorioPedidos repositorio,
        GeneradorComprobante generadorComprobante,
        INotificador notificador)
    {
        _calculadora = calculadora;
        _repositorio = repositorio;
        _generadorComprobante = generadorComprobante;
        _notificador = notificador;
    }

    public void ProcesarPedido(string cliente, string tipoCliente, string medicamento, int cantidad, decimal precioUnitario)
    {
        decimal total = cantidad * precioUnitario;
        decimal descuento = _calculadora.CalcularDescuento(tipoCliente, total);

        var pedido = new Pedido(cliente, tipoCliente, medicamento, cantidad, precioUnitario, descuento);

        _repositorio.GuardarPedido(pedido);
        _generadorComprobante.Generar(pedido);
        _notificador.Notificar(cliente, $"Su pedido de {medicamento} fue registrado, {cliente}");
    }
}

// ---------------------------------------------------------------------------
// Composition root: el único lugar del sistema donde se conectan las
// abstracciones con sus implementaciones concretas.
// ---------------------------------------------------------------------------
public static class DemoRefactor
{
    public static void Correr()
    {
        var gestor = new GestorDePedidos(
            new CalculadoraDescuento(),
            new RepositorioPedidosMySql(),
            new GeneradorComprobante(),
            new NotificadorSmtp());

        gestor.ProcesarPedido("Noelia", "asegurado", "Paracetamol 500mg", 2, 8.50m);
    }
}
