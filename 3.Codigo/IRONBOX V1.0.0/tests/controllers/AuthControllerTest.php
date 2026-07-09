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

}
