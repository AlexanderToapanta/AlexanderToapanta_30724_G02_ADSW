<?php

use PHPUnit\Framework\TestCase;

final class UsuarioServiceAutenticacionTest extends TestCase
{
    private function crearUsuario(string $correo, string $contrasenaPlana, string $estado): Usuario
    {
        return new Usuario(
            1,
            'Kenned Sigcha',
            '1710034065',
            $correo,
            password_hash($contrasenaPlana, PASSWORD_DEFAULT),
            'Atleta',
            $estado,
            '2026-01-01'
        );
    }

    public function test_autentica_usuario_activo_con_credenciales_validas(): void
    {
        $usuario = $this->crearUsuario('kenned@example.com', 'claveSegura1', 'Activo');

        $daoMock = $this->createMock(UsuarioDAO::class);
        $daoMock->method('buscarPorCorreo')->with('kenned@example.com')->willReturn($usuario);

        $service = new UsuarioService($daoMock);
        $resultado = $service->autenticar('kenned@example.com', 'claveSegura1');

        $this->assertSame($usuario, $resultado);
    }

    public function test_rechaza_correo_vacio_o_contrasena_vacia(): void
    {
        $daoMock = $this->createMock(UsuarioDAO::class);
        $service = new UsuarioService($daoMock);

        $this->expectException(InvalidArgumentException::class);
        $service->autenticar('', '');
    }

    public function test_rechaza_contrasena_incorrecta(): void
    {
        $usuario = $this->crearUsuario('kenned@example.com', 'claveSegura1', 'Activo');

        $daoMock = $this->createMock(UsuarioDAO::class);
        $daoMock->method('buscarPorCorreo')->willReturn($usuario);

        $service = new UsuarioService($daoMock);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Credenciales invalidas.');
        $service->autenticar('kenned@example.com', 'claveIncorrecta');
    }
}
