<?php

use PHPUnit\Framework\TestCase;

final class UsuarioBuilderTest extends TestCase
{
    private const CEDULA_VALIDA = '1710034065';

    public function test_crea_usuario_con_datos_validos(): void
    {
        $usuario = (new UsuarioBuilder())
            ->configurarNombre('Kenned Sigcha')
            ->configurarCedula(self::CEDULA_VALIDA)
            ->configurarCorreo('kenned@example.com')
            ->definirContrasena('claveSegura1')
            ->asignarRol('Atleta')
            ->definirEstado('Activo')
            ->definirFechaRegistro('2026-01-01')
            ->construir();

        $this->assertInstanceOf(Usuario::class, $usuario);
        $this->assertSame('Kenned Sigcha', $usuario->getNombre());
        $this->assertSame(self::CEDULA_VALIDA, $usuario->getCedula());
        $this->assertSame('kenned@example.com', $usuario->getCorreo());
        $this->assertSame('Atleta', $usuario->getRol());
        $this->assertSame('Activo', $usuario->getEstado());
        $this->assertSame('2026-01-01', $usuario->getFechaRegistro());
    }

    public function test_rechaza_nombre_menor_a_tres_caracteres(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new UsuarioBuilder())->configurarNombre('Al');
    }

    public function test_rechaza_cedula_ecuatoriana_invalida(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new UsuarioBuilder())->configurarCedula('1234567890');
    }

    public function test_rechaza_correo_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new UsuarioBuilder())->configurarCorreo('no-es-un-correo');
    }

    public function test_hashea_contrasena_correctamente(): void
    {
        $usuario = (new UsuarioBuilder())
            ->configurarNombre('Kenned Sigcha')
            ->configurarCedula(self::CEDULA_VALIDA)
            ->configurarCorreo('kenned@example.com')
            ->definirContrasena('claveSegura1')
            ->asignarRol('Atleta')
            ->definirEstado('Activo')
            ->definirFechaRegistro('2026-01-01')
            ->construir();

        $this->assertNotSame('claveSegura1', $usuario->getContrasena());
        $this->assertTrue(password_verify('claveSegura1', $usuario->getContrasena()));
    }

    public function test_rechaza_contrasena_menor_a_ocho_caracteres(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new UsuarioBuilder())->definirContrasena('1234567');
    }

    public function test_rechaza_rol_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new UsuarioBuilder())->asignarRol('SuperAdmin');
    }

    public function test_rechaza_estado_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new UsuarioBuilder())->definirEstado('Suspendido');
    }
}
