<?php

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../includes/Database.php';

class UsuarioDAO
{
    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? Database::conectar();
    }

    public function crear(Usuario $usuario): Usuario
    {
        $sentencia = $this->conexion->prepare(
            'INSERT INTO usuarios
                (nombre, cedula, correo, contrasena, rol, estado, fecha_registro)
             VALUES
                (:nombre, :cedula, :correo, :contrasena, :rol, :estado, :fecha_registro)'
        );

        $sentencia->execute([
            'nombre' => $usuario->getNombre(),
            'cedula' => $usuario->getCedula(),
            'correo' => $usuario->getCorreo(),
            'contrasena' => $usuario->getContrasena(),
            'rol' => $usuario->getRol(),
            'estado' => $usuario->getEstado(),
            'fecha_registro' => $usuario->getFechaRegistro(),
        ]);

        $usuarioCreado = $this->buscarPorId((int) $this->conexion->lastInsertId());
        if ($usuarioCreado) {
            $this->sincronizarPersonaPorRol($usuarioCreado);
        }

        return $usuarioCreado;
    }

    public function actualizar(Usuario $usuario): Usuario
    {
        $sentencia = $this->conexion->prepare(
            'UPDATE usuarios
                SET nombre = :nombre,
                    cedula = :cedula,
                    correo = :correo,
                    contrasena = :contrasena,
                    rol = :rol,
                    estado = :estado,
                    fecha_registro = :fecha_registro
              WHERE id = :id'
        );

        $sentencia->execute([
            'id' => $usuario->getId(),
            'nombre' => $usuario->getNombre(),
            'cedula' => $usuario->getCedula(),
            'correo' => $usuario->getCorreo(),
            'contrasena' => $usuario->getContrasena(),
            'rol' => $usuario->getRol(),
            'estado' => $usuario->getEstado(),
            'fecha_registro' => $usuario->getFechaRegistro(),
        ]);

        $usuarioActualizado = $this->buscarPorId((int) $usuario->getId());
        if ($usuarioActualizado) {
            $this->sincronizarPersonaPorRol($usuarioActualizado);
        }

        return $usuarioActualizado;
    }

    public function desactivar(int $id): bool
    {
        $sentencia = $this->conexion->prepare(
            "UPDATE usuarios SET estado = 'Inactivo' WHERE id = :id AND estado = 'Activo'"
        );
        $sentencia->execute(['id' => $id]);

        return $sentencia->rowCount() > 0;
    }

    public function buscarPorId(int $id): ?Usuario
    {
        $sentencia = $this->conexion->prepare('SELECT * FROM usuarios WHERE id = :id');
        $sentencia->execute(['id' => $id]);
        $fila = $sentencia->fetch();

        return $fila ? Usuario::fromArray($fila) : null;
    }

    public function buscarPorCorreo(string $correo): ?Usuario
    {
        $sentencia = $this->conexion->prepare('SELECT * FROM usuarios WHERE lower(correo) = :correo LIMIT 1');
        $sentencia->execute(['correo' => strtolower(trim($correo))]);
        $fila = $sentencia->fetch();

        return $fila ? Usuario::fromArray($fila) : null;
    }

    public function listar(): array
    {
        $sentencia = $this->conexion->query(
            'SELECT * FROM usuarios ORDER BY estado ASC, rol ASC, nombre ASC'
        );

        return array_map(
            fn (array $fila): array => Usuario::fromArray($fila)->toArray(),
            $sentencia->fetchAll()
        );
    }

    public function correoExiste(string $correo, ?int $idIgnorado = null): bool
    {
        $parametros = ['correo' => strtolower(trim($correo))];
        $sql = 'SELECT COUNT(*) FROM usuarios WHERE lower(correo) = :correo';

        if ($idIgnorado !== null) {
            $sql .= ' AND id <> :id_ignorado';
            $parametros['id_ignorado'] = $idIgnorado;
        }

        $sentencia = $this->conexion->prepare($sql);
        $sentencia->execute($parametros);

        return (int) $sentencia->fetchColumn() > 0;
    }

    public function cedulaExiste(string $cedula, ?int $idIgnorado = null): bool
    {
        $parametros = ['cedula' => preg_replace('/\D+/', '', $cedula)];
        $sql = 'SELECT COUNT(*) FROM usuarios WHERE cedula = :cedula';

        if ($idIgnorado !== null) {
            $sql .= ' AND id <> :id_ignorado';
            $parametros['id_ignorado'] = $idIgnorado;
        }

        $sentencia = $this->conexion->prepare($sql);
        $sentencia->execute($parametros);

        return (int) $sentencia->fetchColumn() > 0;
    }

    public function sincronizarPersonaPorRol(Usuario $usuario): void
    {
        if ($usuario->getRol() === 'Atleta') {
            $sentencia = $this->conexion->prepare(
                'INSERT INTO atletas (nombre, correo, fecha_registro)
                 VALUES (:nombre, :correo, :fecha_registro)
                 ON DUPLICATE KEY UPDATE
                    nombre = VALUES(nombre),
                    fecha_registro = VALUES(fecha_registro)'
            );
            $sentencia->execute([
                'nombre' => $usuario->getNombre(),
                'correo' => $usuario->getCorreo(),
                'fecha_registro' => $usuario->getFechaRegistro(),
            ]);
        }

        if ($usuario->getRol() === 'Entrenador') {
            $sentencia = $this->conexion->prepare(
                'INSERT INTO entrenadores (nombre, correo, disponible)
                 VALUES (:nombre, :correo, 1)
                 ON DUPLICATE KEY UPDATE
                    nombre = VALUES(nombre),
                    disponible = 1'
            );
            $sentencia->execute([
                'nombre' => $usuario->getNombre(),
                'correo' => $usuario->getCorreo(),
            ]);
        }
    }
}
