<?php

use PHPUnit\Framework\TestCase;

final class MembresiaBuilderTest extends TestCase
{
    public function test_crea_membresia_valida(): void
    {
        $membresia = (new MembresiaBuilder())
            ->asignarAtleta(1)
            ->configurarPlan('Mensual', 50.5)
            ->definirFechaInicio('2026-01-01')
            ->definirEstado('Pendiente')
            ->construir();

        $this->assertInstanceOf(Membresia::class, $membresia);
        $this->assertSame(1, $membresia->getIdAtleta());
        $this->assertSame('Mensual', $membresia->getTipo());
        $this->assertSame(50.5, $membresia->getPrecio());
        $this->assertSame('2026-01-01', $membresia->getFechaInicio());
        $this->assertSame('Pendiente', $membresia->getEstado());
    }

    public function test_rechaza_atleta_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new MembresiaBuilder())->asignarAtleta(0);
    }

    public function test_rechaza_tipo_vacio(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new MembresiaBuilder())->configurarPlan('   ', 50);
    }

    public function test_rechaza_precio_cero_o_negativo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new MembresiaBuilder())->configurarPlan('Mensual', -10);
    }

    public function test_redondea_precio_a_dos_decimales(): void
    {
        $membresia = (new MembresiaBuilder())
            ->asignarAtleta(1)
            ->configurarPlan('Mensual', 49.995)
            ->definirFechaInicio('2026-01-01')
            ->definirEstado('Pendiente')
            ->construir();

        $this->assertSame(50.0, $membresia->getPrecio());
    }

    public function test_rechaza_fecha_inicio_invalida(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new MembresiaBuilder())->definirFechaInicio('01-01-2026');
    }

    public function test_calcula_vencimiento_a_treinta_dias(): void
    {
        $membresia = (new MembresiaBuilder())
            ->asignarAtleta(1)
            ->configurarPlan('Mensual', 50)
            ->definirFechaInicio('2026-01-01')
            ->definirEstado('Pendiente')
            ->construir();

        $this->assertSame('2026-01-31', $membresia->getFechaVencimiento());
    }

    public function test_marcar_como_pagado_actualiza_estado_inicio_y_vencimiento(): void
    {
        $membresia = (new MembresiaBuilder())
            ->asignarAtleta(1)
            ->configurarPlan('Mensual', 50)
            ->marcarComoPagadoDesde('2026-03-10')
            ->construir();

        $this->assertSame('Pagado', $membresia->getEstado());
        $this->assertSame('2026-03-10', $membresia->getFechaInicio());
        $this->assertSame('2026-04-09', $membresia->getFechaVencimiento());
    }

    public function test_rechaza_estado_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new MembresiaBuilder())->definirEstado('Suspendida');
    }

    public function test_rechaza_vencimiento_anterior_a_inicio(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new MembresiaBuilder())
            ->asignarAtleta(1)
            ->configurarPlan('Mensual', 50)
            ->definirFechaInicio('2026-02-01')
            ->definirFechaVencimiento('2026-01-01')
            ->definirEstado('Pendiente')
            ->construir();
    }
}
