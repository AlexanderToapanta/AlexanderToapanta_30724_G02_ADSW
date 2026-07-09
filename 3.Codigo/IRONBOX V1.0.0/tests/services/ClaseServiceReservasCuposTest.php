<?php

use PHPUnit\Framework\TestCase;

final class ClaseServiceReservasCuposTest extends TestCase
{
    private function membresiaPagadaVigente(int $idAtleta): Membresia
    {
        return new Membresia(
            1,
            'Mensual',
            50.0,
            date('Y-m-d', strtotime('-5 days')),
            date('Y-m-d', strtotime('+25 days')),
            'Pagado',
            $idAtleta
        );
    }

    public function test_listar_clases_disponibles_valida_atleta(): void
    {
        $claseDaoMock = $this->createMock(ClaseDAO::class);
        $claseDaoMock->method('atletaExiste')->with(10)->willReturn(true);
        $claseDaoMock->method('listarClasesDisponibles')->with(10)->willReturn([
            ['id' => 1, 'dia' => date('Y-m-d', strtotime('+2 days')), 'hora' => '08:00'],
        ]);

        $membresiaDaoMock = $this->createMock(MembresiaDAO::class);
        $service = new ClaseService($claseDaoMock, $membresiaDaoMock);

        $resultado = $service->listarClasesDisponibles(10);
        $this->assertCount(1, $resultado);
    }

    public function test_reservar_cupo_con_membresia_pagada_y_vigente(): void
    {
        $reserva = new Reserva(15, 10, 7, date('Y-m-d H:i:s'), 'Confirmada');

        $claseDaoMock = $this->createMock(ClaseDAO::class);
        $claseDaoMock->method('atletaExiste')->with(10)->willReturn(true);
        $claseDaoMock->method('claseExiste')->with(7)->willReturn(true);
        $claseDaoMock->method('consultarCupos')->with(7)->willReturn(3);
        $claseDaoMock->method('existeReservaActiva')->with(10, 7)->willReturn(false);
        $claseDaoMock->method('reservarCupo')->with(10, 7)->willReturn($reserva);

        $membresiaDaoMock = $this->createMock(MembresiaDAO::class);
        $membresiaDaoMock->method('buscarActualPorAtleta')->with(10)->willReturn($this->membresiaPagadaVigente(10));

        $service = new ClaseService($claseDaoMock, $membresiaDaoMock);
        $resultado = $service->reservar(['idAtleta' => 10, 'idClase' => 7]);

        $this->assertSame($reserva, $resultado);
    }

    public function test_reservar_falla_si_atleta_no_existe(): void
    {
        $claseDaoMock = $this->createMock(ClaseDAO::class);
        $claseDaoMock->method('atletaExiste')->with(99)->willReturn(false);

        $membresiaDaoMock = $this->createMock(MembresiaDAO::class);
        $service = new ClaseService($claseDaoMock, $membresiaDaoMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Debe seleccionar un atleta valido.');
        $service->reservar(['idAtleta' => 99, 'idClase' => 1]);
    }

    public function test_reservar_falla_si_clase_no_existe(): void
    {
        $claseDaoMock = $this->createMock(ClaseDAO::class);
        $claseDaoMock->method('atletaExiste')->with(10)->willReturn(true);
        $claseDaoMock->method('claseExiste')->with(999)->willReturn(false);

        $membresiaDaoMock = $this->createMock(MembresiaDAO::class);
        $service = new ClaseService($claseDaoMock, $membresiaDaoMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Debe seleccionar una clase valida.');
        $service->reservar(['idAtleta' => 10, 'idClase' => 999]);
    }

    public function test_reservar_falla_si_membresia_no_esta_vigente(): void
    {
        $membresiaVencida = new Membresia(
            2,
            'Mensual',
            50.0,
            date('Y-m-d', strtotime('-40 days')),
            date('Y-m-d', strtotime('-10 days')),
            'Pagado',
            10
        );

        $claseDaoMock = $this->createMock(ClaseDAO::class);
        $claseDaoMock->method('atletaExiste')->with(10)->willReturn(true);
        $claseDaoMock->method('claseExiste')->with(7)->willReturn(true);

        $membresiaDaoMock = $this->createMock(MembresiaDAO::class);
        $membresiaDaoMock->method('buscarActualPorAtleta')->with(10)->willReturn($membresiaVencida);

        $service = new ClaseService($claseDaoMock, $membresiaDaoMock);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('El atleta no tiene una membresia pagada y vigente.');
        $service->reservar(['idAtleta' => 10, 'idClase' => 7]);
    }

    public function test_reservar_falla_si_no_hay_cupos(): void
    {
        $claseDaoMock = $this->createMock(ClaseDAO::class);
        $claseDaoMock->method('atletaExiste')->with(10)->willReturn(true);
        $claseDaoMock->method('claseExiste')->with(7)->willReturn(true);
        $claseDaoMock->method('consultarCupos')->with(7)->willReturn(0);

        $membresiaDaoMock = $this->createMock(MembresiaDAO::class);
        $membresiaDaoMock->method('buscarActualPorAtleta')->with(10)->willReturn($this->membresiaPagadaVigente(10));

        $service = new ClaseService($claseDaoMock, $membresiaDaoMock);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('La clase seleccionada no tiene cupos disponibles.');
        $service->reservar(['idAtleta' => 10, 'idClase' => 7]);
    }

    public function test_reservar_falla_si_ya_existe_reserva_activa(): void
    {
        $claseDaoMock = $this->createMock(ClaseDAO::class);
        $claseDaoMock->method('atletaExiste')->with(10)->willReturn(true);
        $claseDaoMock->method('claseExiste')->with(7)->willReturn(true);
        $claseDaoMock->method('consultarCupos')->with(7)->willReturn(2);
        $claseDaoMock->method('existeReservaActiva')->with(10, 7)->willReturn(true);

        $membresiaDaoMock = $this->createMock(MembresiaDAO::class);
        $membresiaDaoMock->method('buscarActualPorAtleta')->with(10)->willReturn($this->membresiaPagadaVigente(10));

        $service = new ClaseService($claseDaoMock, $membresiaDaoMock);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('El atleta ya tiene una reserva activa para esta clase.');
        $service->reservar(['idAtleta' => 10, 'idClase' => 7]);
    }

}
