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

    public function test_rechaza_id_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ClaseBuilder())->conId(0);
    }

    public function test_rechaza_fecha_invalida(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ClaseBuilder())->definirDiaHora('01-08-2026', '10:00');
    }

    public function test_rechaza_hora_invalida(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ClaseBuilder())->definirDiaHora($this->fechaFutura(), '25:00');
    }

    public function test_rechaza_fecha_u_hora_pasada(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ClaseBuilder())->definirDiaHora(date('Y-m-d', strtotime('-1 day')), '10:00');
    }

    public function test_rechaza_duracion_cero_o_negativa(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ClaseBuilder())->definirDuracion(0);
    }

    public function test_rechaza_duracion_mayor_a_240(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ClaseBuilder())->definirDuracion(241);
    }

    public function test_rechaza_cupo_mayor_a_capacidad(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ClaseBuilder())->definirCupoMaximo(31, 30);
    }

    public function test_rechaza_entrenador_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ClaseBuilder())->asignarEntrenador(0);
    }

    public function test_rechaza_cupos_disponibles_mayores_al_cupo_maximo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ClaseBuilder())
            ->definirDiaHora($this->fechaFutura(), '10:00')
            ->definirDuracion(60)
            ->definirCupoMaximo(20, 30)
            ->definirCuposDisponibles(21)
            ->asignarEntrenador(5)
            ->construir();
    }

}
