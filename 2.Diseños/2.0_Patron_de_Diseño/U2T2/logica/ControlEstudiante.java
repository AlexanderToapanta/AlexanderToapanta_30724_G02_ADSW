package U2T2.logica;

import U2T2.datos.RepositorioEstudiante;
import U2T2.datos.EstudianteExterno;
import U2T2.modelo.Estudiante;
import java.util.List;

/**
 * CAPA: Logica de Negocio
 * Responsabilidad: coordina el CRUD entre Presentacion y Datos.
 * Patrones aplicados:
 * Adapter: convierte datos externos codigo/nombreCompleto/anios al modelo interno Estudiante.
 * Decorator: agrega validacion, reglas academicas y auditoria al CRUD base.
 */
public class ControlEstudiante {
    private final ServicioCrudEstudiante servicioCrud;
    private final AdaptadorEntradaEstudiante adaptadorEntrada;

    public ControlEstudiante() {
        RepositorioEstudiante repo = new RepositorioEstudiante();
        ServicioCrudEstudiante base = new CrudEstudianteBase(repo);
        ServicioCrudEstudiante validado = new ValidacionCrudEstudianteDecorator(base);
        this.servicioCrud = new AuditoriaCrudEstudianteDecorator(validado);
        this.adaptadorEntrada = new AdaptadorEstudianteExterno();
    }

    // RF-01: CREATE usando Adapter para transformar la entrada externa al modelo interno.
    public String agregarDesdeEntradaExterna(int codigo, String nombreCompleto, int anios) {
        try {
            EstudianteExterno entrada = new EstudianteExterno(codigo, nombreCompleto, anios);
            Estudiante estudiante = adaptadorEntrada.adaptar(entrada);
            return servicioCrud.agregar(estudiante);
        } catch (IllegalArgumentException ex) {
            return "Error: " + ex.getMessage();
        }
    }

    // RF-04: READ
    public List<Estudiante> listarTodos() {
        return servicioCrud.listarTodos();
    }

    // RF-02: UPDATE usando la cadena Decorator del CRUD.
    public String actualizarEstudiante(int id, String nuevoNombre, int nuevaEdad) {
        Estudiante estudiante = new Estudiante(id, nuevoNombre, nuevaEdad);
        return servicioCrud.actualizar(estudiante);
    }

    // RF-03: DELETE usando la cadena Decorator del CRUD.
    public String eliminarEstudiante(int id) {
        return servicioCrud.eliminar(id);
    }
}
