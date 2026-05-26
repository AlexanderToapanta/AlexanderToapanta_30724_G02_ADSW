package Implementacion;

import java.util.List;

public class ControlEstudiante {
    private final RepositorioEstudiante repo = new RepositorioEstudiante();
    private IPrototipoEstudiante estudiantePrototipo; // Plantilla base

    public ControlEstudiante(IPrototipoEstudiante prototipo) {
        this.estudiantePrototipo = prototipo;
    }

    // CREATE usando Builder
    public String agregarConBuilder(int id, String nombre, int edad) {
        if (!validarDatos(id, nombre, edad) || repo.existeId(id)) {
            return "Error: Datos invalidos o ID ya existe.";
        }

        Estudiante nuevo = new EstudianteBuilder()
                .conId(id)
                .conNombre(nombre)
                .conEdad(edad)
                .build();

        repo.guardar(nuevo);
        return "Agregado exitosamente (Via Builder): " + nuevo.getNombre();
    }

    // CREATE usando Prototype
    public String agregarConPrototipo(int id, String nombre, int edad) {
        if (!validarDatos(id, nombre, edad) || repo.existeId(id)) {
            return "Error: Datos invalidos o ID ya existe.";
        }

        Estudiante clon = estudiantePrototipo.clonar();
        clon.setId(id);
        clon.setNombre(nombre);
        clon.setEdad(edad);

        repo.guardar(clon);
        return "Agregado exitosamente (Via Prototype): " + clon.getNombre();
    }

    // READ
    public List<Estudiante> listarTodos() {
        return repo.listarTodos();
    }

    // UPDATE utilizando clonación para validar como borrador
    public String actualizarEstudiante(int id, String nuevoNombre, int nuevaEdad) {
        Estudiante original = repo.buscarPorId(id);
        if (original != null && validarDatos(id, nuevoNombre, nuevaEdad)) {
            Estudiante borrador = original.clonar();
            borrador.setNombre(nuevoNombre);
            borrador.setEdad(nuevaEdad);

            repo.actualizar(borrador);
            return "Actualizado exitosamente: ID " + id;
        } else {
            return "Error: Estudiante no encontrado o datos invalidos.";
        }
    }

    // DELETE
    public String eliminarEstudiante(int id) {
        if (repo.existeId(id)) {
            repo.eliminar(id);
            return "Eliminado exitosamente: ID " + id;
        } else {
            return "Error: Estudiante no encontrado.";
        }
    }

    public boolean validarDatos(int id, String nombre, int edad) {
        return id > 0 && nombre != null && !nombre.isEmpty() && edad > 0;
    }
}
