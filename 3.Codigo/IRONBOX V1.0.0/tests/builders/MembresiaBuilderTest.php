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
}
