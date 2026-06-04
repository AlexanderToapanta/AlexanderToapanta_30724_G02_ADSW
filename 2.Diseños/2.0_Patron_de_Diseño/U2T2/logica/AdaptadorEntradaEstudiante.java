package U2T2.logica;

import U2T2.datos.EstudianteExterno;
import U2T2.modelo.Estudiante;

public interface AdaptadorEntradaEstudiante {
    Estudiante adaptar(EstudianteExterno entrada);
}
