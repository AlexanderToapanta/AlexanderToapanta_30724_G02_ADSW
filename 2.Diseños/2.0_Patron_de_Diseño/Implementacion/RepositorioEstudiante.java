package Implementacion;

import java.util.ArrayList;
import java.util.List;

public class RepositorioEstudiante {
    private final List<Estudiante> estudiantes = new ArrayList<>();

    public boolean existeId(int id) {
        return estudiantes.stream().anyMatch(e -> e.getId() == id);
    }

    public void guardar(Estudiante estudiante) {
        estudiantes.add(estudiante);
    }

    public Estudiante buscarPorId(int id) {
        return estudiantes.stream().filter(e -> e.getId() == id).findFirst().orElse(null);
    }

    public void actualizar(Estudiante estudiante) {
        Estudiante existente = buscarPorId(estudiante.getId());
        if (existente != null) {
            existente.setNombre(estudiante.getNombre());
            existente.setEdad(estudiante.getEdad());
        }
    }

    public void eliminar(int id) {
        estudiantes.removeIf(e -> e.getId() == id);
    }

    // Devolver una copia para proteger la lista interna
    public List<Estudiante> listarTodos() {
        return new ArrayList<>(estudiantes);
    }
}
