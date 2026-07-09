<?php

use PHPUnit\Framework\TestCase;

final class ClaseBuilderTest extends TestCase
{
    private function fechaFutura(): string
    {
        return date('Y-m-d', strtotime('+7 days'));
    }

    private function construirClaseValida(): Clase
    {
        return (new ClaseBuilder())
            ->conId(1)
            ->definirDiaHora($this->fechaFutura(), '10:00')
            ->definirDuracion(60)
            ->definirCupoMaximo(20, 30)
            ->asignarEntrenador(5)
            ->construir();
    }

    public function test_crea_clase_valida(): void
    {
        $clase = $this->construirClaseValida();

        $this->assertInstanceOf(Clase::class, $clase);
        $this->assertSame(1, $clase->getId());
        $this->assertSame(20, $clase->getCupoMaximo());
        $this->assertSame(20, $clase->getCuposDisponibles());
    }

}
