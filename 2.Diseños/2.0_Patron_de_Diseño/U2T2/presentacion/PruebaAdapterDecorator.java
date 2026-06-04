package U2T2.presentacion;

import U2T2.logica.ControlEstudiante;

public class PruebaAdapterDecorator {
    public static void main(String[] args) {
        ControlEstudiante control = new ControlEstudiante();

        System.out.println("=== Adapter: entrada externa codigo/nombreCompleto/anios ===");
        System.out.println(control.agregarDesdeEntradaExterna(1, "Ana Torres", 20));
        System.out.println(control.agregarDesdeEntradaExterna(2, "Luis Perez", 17));
        System.out.println(control.agregarDesdeEntradaExterna(1, "Codigo Repetido", 22));

        System.out.println("\n=== Decorator: validacion y auditoria ===");
        System.out.println(control.actualizarEstudiante(1, "Ana Torres Actualizada", 21));
        System.out.println(control.eliminarEstudiante(2));
        System.out.println(control.eliminarEstudiante(1));

        System.out.println("\n=== Lista final ===");
        control.listarTodos().forEach(System.out::println);
    }
}
