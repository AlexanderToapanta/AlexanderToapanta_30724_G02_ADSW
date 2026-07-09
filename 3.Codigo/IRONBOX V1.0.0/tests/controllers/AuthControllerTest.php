<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/Auth.php';

final class AuthControllerTest extends TestCase
{
    private function crearUsuario(string $rol, string $correo): Usuario
    {
        return new Usuario(
            1,
            'Usuario ' . $rol,
            '1710034065',
            $correo,
            password_hash('claveSegura123', PASSWORD_DEFAULT),
            $rol,
            'Activo',
            '2026-01-01'
        );
    }

    public function test_login_administrador_devuelve_rol_administrador(): void
    {
        $usuario = $this->crearUsuario('Administrador', 'admin@example.com');

        $daoMock = $this->createMock(UsuarioDAO::class);
        $daoMock->method('buscarPorCorreo')->with('admin@example.com')->willReturn($usuario);

        $service = new UsuarioService($daoMock);
        $resultado = $service->autenticar('admin@example.com', 'claveSegura123');

        $this->assertSame('Administrador', $resultado->getRol());
    }

    public function test_login_entrenador_devuelve_rol_entrenador(): void
    {
        $usuario = $this->crearUsuario('Entrenador', 'coach@example.com');

        $daoMock = $this->createMock(UsuarioDAO::class);
        $daoMock->method('buscarPorCorreo')->with('coach@example.com')->willReturn($usuario);

        $service = new UsuarioService($daoMock);
        $resultado = $service->autenticar('coach@example.com', 'claveSegura123');

        $this->assertSame('Entrenador', $resultado->getRol());
    }

    public function test_login_atleta_devuelve_rol_atleta(): void
    {
        $usuario = $this->crearUsuario('Atleta', 'atleta@example.com');

        $daoMock = $this->createMock(UsuarioDAO::class);
        $daoMock->method('buscarPorCorreo')->with('atleta@example.com')->willReturn($usuario);

        $service = new UsuarioService($daoMock);
        $resultado = $service->autenticar('atleta@example.com', 'claveSegura123');

        $this->assertSame('Atleta', $resultado->getRol());
    }

    public function test_me_sin_sesion_devuelve_no_autenticado(): void
    {
        authCerrarSesion();
        $usuario = authUsuarioActual();

        $this->assertNull($usuario);
    }

}
