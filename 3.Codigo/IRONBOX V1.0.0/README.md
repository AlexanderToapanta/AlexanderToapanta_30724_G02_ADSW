# IronClad Box

Sistema web para la gestión operativa de un gimnasio **CrossFit**: usuarios, clases, membresías, reservas, progreso deportivo, comunicación interna y reportes. Prototipo funcional construido con **PHP nativo** (sin frameworks) sobre una arquitectura por capas y patrón **MVC + Builder**.

## Roles

- **Administrador** — gestiona usuarios, clases, membresías, pagos y reportes.
- **Entrenador** — programa clases, registra el progreso deportivo de los atletas y se comunica con ellos.
- **Atleta** — reserva cupos en clases, registra su progreso personal, consulta y gestiona su membresía, y lee mensajes.

## Stack y arquitectura

| Área | Decisión |
| --- | --- |
| Frontend | HTML5, CSS3 y JavaScript puro (Vanilla JS), Chart.js por CDN |
| Backend | PHP nativo (8.2+) |
| Acceso a datos | PDO con sentencias preparadas |
| Base de datos | MySQL |
| Arquitectura | Capas MVC: View → Controller → Service → Builder → DAO → Model |
| Autenticación | Sesión PHP (`includes/Auth.php` + `AuthController.php`) |
| Navegación | Dashboards por rol (`assets/js/nav.js`) |
