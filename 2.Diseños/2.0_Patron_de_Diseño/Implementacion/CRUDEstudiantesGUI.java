package Implementacion;

import javax.swing.*;
import java.awt.*;
import java.awt.event.ActionEvent;
import java.util.List;

public class CRUDEstudiantesGUI extends JFrame {
    private final ControlEstudiante control;

    private final JTextField txtId = new JTextField(10);
    private final JTextField txtNombre = new JTextField(20);
    private final JTextField txtEdad = new JTextField(5);
    private final JTextArea areaSalida = new JTextArea(12, 40);

    public CRUDEstudiantesGUI() {
        // Prototipo por defecto
        Estudiante prototipo = new Estudiante(0, "Sin Nombre", 18);
        this.control = new ControlEstudiante(prototipo);

        setTitle("CRUD Estudiantes - Patrones");
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        initLayout();
        pack();
        setLocationRelativeTo(null);
    }

    private void initLayout() {
        JPanel panel = new JPanel(new BorderLayout(8,8));

        JPanel form = new JPanel(new FlowLayout(FlowLayout.LEFT));
        form.add(new JLabel("ID:")); form.add(txtId);
        form.add(new JLabel("Nombre:")); form.add(txtNombre);
        form.add(new JLabel("Edad:")); form.add(txtEdad);

        JPanel botones = new JPanel(new FlowLayout(FlowLayout.LEFT));
        JButton btnAgregarBuilder = new JButton("Agregar (Builder)");
        JButton btnAgregarProto = new JButton("Agregar (Prototype)");
        JButton btnActualizar = new JButton("Actualizar");
        JButton btnEliminar = new JButton("Eliminar");
        JButton btnListar = new JButton("Listar Todos");

        botones.add(btnAgregarBuilder);
        botones.add(btnAgregarProto);
        botones.add(btnActualizar);
        botones.add(btnEliminar);
        botones.add(btnListar);

        areaSalida.setEditable(false);
        JScrollPane scroll = new JScrollPane(areaSalida);

        panel.add(form, BorderLayout.NORTH);
        panel.add(botones, BorderLayout.CENTER);
        panel.add(scroll, BorderLayout.SOUTH);

        setContentPane(panel);

        // Listeners
        btnAgregarBuilder.addActionListener(this::onAgregarBuilder);
        btnAgregarProto.addActionListener(this::onAgregarProto);
        btnActualizar.addActionListener(this::onActualizar);
        btnEliminar.addActionListener(this::onEliminar);
        btnListar.addActionListener(e -> actualizarLista());
    }

    private void onAgregarBuilder(ActionEvent e) {
        try {
            int id = Integer.parseInt(txtId.getText().trim());
            String nombre = txtNombre.getText().trim();
            int edad = Integer.parseInt(txtEdad.getText().trim());
            String res = control.agregarConBuilder(id, nombre, edad);
            mostrarMensaje(res);
            actualizarLista();
        } catch (NumberFormatException ex) {
            mostrarMensaje("ID y Edad deben ser numeros enteros.");
        } catch (IllegalArgumentException ex) {
            mostrarMensaje(ex.getMessage());
        }
    }

    private void onAgregarProto(ActionEvent e) {
        try {
            int id = Integer.parseInt(txtId.getText().trim());
            String nombre = txtNombre.getText().trim();
            int edad = Integer.parseInt(txtEdad.getText().trim());
            String res = control.agregarConPrototipo(id, nombre, edad);
            mostrarMensaje(res);
            actualizarLista();
        } catch (NumberFormatException ex) {
            mostrarMensaje("ID y Edad deben ser numeros enteros.");
        }
    }

    private void onActualizar(ActionEvent e) {
        try {
            int id = Integer.parseInt(txtId.getText().trim());
            String nombre = txtNombre.getText().trim();
            int edad = Integer.parseInt(txtEdad.getText().trim());
            String res = control.actualizarEstudiante(id, nombre, edad);
            mostrarMensaje(res);
            actualizarLista();
        } catch (NumberFormatException ex) {
            mostrarMensaje("ID y Edad deben ser numeros enteros.");
        }
    }

    private void onEliminar(ActionEvent e) {
        try {
            int id = Integer.parseInt(txtId.getText().trim());
            String res = control.eliminarEstudiante(id);
            mostrarMensaje(res);
            actualizarLista();
        } catch (NumberFormatException ex) {
            mostrarMensaje("ID debe ser un numero entero.");
        }
    }

    private void actualizarLista() {
        List<Estudiante> lista = control.listarTodos();
        StringBuilder sb = new StringBuilder();
        sb.append("--- LISTA DE ESTUDIANTES ---\n");
        for (Estudiante e : lista) sb.append(e).append('\n');
        areaSalida.setText(sb.toString());
    }

    private void mostrarMensaje(String msg) {
        JOptionPane.showMessageDialog(this, msg);
    }

    public static void main(String[] args) {
        SwingUtilities.invokeLater(() -> {
            CRUDEstudiantesGUI gui = new CRUDEstudiantesGUI();
            gui.setVisible(true);
        });
    }
}
