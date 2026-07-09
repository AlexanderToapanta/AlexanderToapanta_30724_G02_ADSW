<?php

use PHPUnit\Framework\TestCase;

final class UsuarioServiceEdicionTest extends TestCase
{
    private function crearUsuarioActual(int $id = 1): Usuario
    {
        return new Usuario(
            $id,
            'Josue Almeida',
            '1710034065',
            'josue@example.com',
            password_hash('claveActual123', PASSWORD_DEFAULT),
            'Atleta',
            'Activo',
            '2026-01-01'
        );
    }

    public function test_edita_usuario_con_datos_validos(): void
    {
        $usuarioActual = $this->crearUsuarioActual(5);

        $daoMock = $this->createMock(UsuarioDAO::class);
        $daoMock->method('buscarPorId')->with(5)->willReturn($usuarioActual);
        $daoMock->method('correoExiste')->with('nuevo@example.com', 5)->willReturn(false);
        $daoMock->method('cedulaExiste')->with('1710034065', 5)->willReturn(false);
        $daoMock->method('actualizar')->willReturnArgument(0);

        $service = new UsuarioService($daoMock);
        $resultado = $service->editar(5, [
            'nombre' => 'Josue Editado',
            'cedula' => '1710034065',
            'correo' => 'nuevo@example.com',
            'rol' => 'Entrenador',
        ]);

        $this->assertSame(5, $resultado->getId());
        $this->assertSame('Josue Editado', $resultado->getNombre());
        $this->assertSame('1710034065', $resultado->getCedula());
        $this->assertSame('nuevo@example.com', $resultado->getCorreo());
        $this->assertSame('Entrenador', $resultado->getRol());
    }

    public function test_editar_falla_si_usuario_no_existe(): void
    {
        $daoMock = $this->createMock(UsuarioDAO::class);
        $daoMock->method('buscarPorId')->with(99)->willReturn(null);

        $service = new UsuarioService($daoMock);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('El usuario solicitado no existe.');
        $service->editar(99, ['nombre' => 'No existe']);
    }

    public function test_editar_mantiene_contrasena_si_no_se_envia_nueva(): void
    {
        $usuarioActual = $this->crearUsuarioActual(7);
        $hashActual = $usuarioActual->getContrasena();

        $daoMock = $this->createMock(UsuarioDAO::class);
        $daoMock->method('buscarPorId')->with(7)->willReturn($usuarioActual);
        $daoMock->method('correoExiste')->willReturn(false);
        $daoMock->method('cedulaExiste')->willReturn(false);
        $daoMock->expects($this->once())
            ->method('actualizar')
            ->with($this->callback(function (Usuario $usuario) use ($hashActual): bool {
                return $usuario->getContrasena() === $hashActual;
            }))
            ->willReturnArgument(0);

        $service = new UsuarioService($daoMock);
        $service->editar(7, ['nombre' => 'Sin cambio de clave']);

        $this->assertTrue(true);
    }

}
