<?php

use PHPUnit\Framework\TestCase;

final class MembresiaServiceEstadoTest extends TestCase
{
    private function crearMembresia(
        int $id,
        int $idAtleta,
        string $estado,
        string $fechaInicio = '2026-01-01',
        string $fechaVencimiento = '2026-01-31',
        string $tipo = 'Mensual',
        float $precio = 50.0
    ): Membresia {
        return new Membresia($id, $tipo, $precio, $fechaInicio, $fechaVencimiento, $estado, $idAtleta);
    }

    public function test_registrar_pago_por_id_membresia_cambia_a_pagado(): void
    {
        $membresiaActual = $this->crearMembresia(1, 10, 'Pendiente');

        $daoMock = $this->createMock(MembresiaDAO::class);
        $daoMock->method('buscarPorId')->with(1)->willReturn($membresiaActual);
        $daoMock->expects($this->once())
            ->method('actualizarTrasPago')
            ->with($this->callback(function (Membresia $membresia): bool {
                return $membresia->getEstado() === 'Pagado'
                    && $membresia->getFechaInicio() === '2026-02-01'
                    && $membresia->getFechaVencimiento() === '2026-03-03';
            }))
            ->willReturnArgument(0);

        $service = new MembresiaService($daoMock);
        $resultado = $service->registrarPago(['id' => 1, 'fechaPago' => '2026-02-01']);

        $this->assertSame('Pagado', $resultado->getEstado());
    }

    public function test_registrar_pago_por_id_atleta_busca_membresia_actual(): void
    {
        $membresiaActual = $this->crearMembresia(2, 20, 'Pendiente');

        $daoMock = $this->createMock(MembresiaDAO::class);
        $daoMock->expects($this->never())->method('buscarPorId');
        $daoMock->expects($this->once())
            ->method('buscarActualPorAtleta')
            ->with(20)
            ->willReturn($membresiaActual);
        $daoMock->method('actualizarTrasPago')->willReturnArgument(0);

        $service = new MembresiaService($daoMock);
        $resultado = $service->registrarPago(['idAtleta' => 20]);

        $this->assertSame(20, $resultado->getIdAtleta());
        $this->assertSame('Pagado', $resultado->getEstado());
    }

    public function test_registrar_pago_falla_si_no_existe_membresia(): void
    {
        $daoMock = $this->createMock(MembresiaDAO::class);
        $daoMock->method('buscarPorId')->willReturn(null);

        $service = new MembresiaService($daoMock);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No existe una membresia asignada para registrar el pago.');
        $service->registrarPago(['id' => 99]);
    }

    public function test_cancelar_membresia_existente(): void
    {
        $membresiaActual = $this->crearMembresia(3, 30, 'Pagado');
        $membresiaCancelada = $this->crearMembresia(3, 30, 'Cancelada');

        $daoMock = $this->createMock(MembresiaDAO::class);
        $daoMock->method('buscarPorId')->with(3)->willReturn($membresiaActual);
        $daoMock->expects($this->once())
            ->method('cancelar')
            ->with(3)
            ->willReturn($membresiaCancelada);

        $service = new MembresiaService($daoMock);
        $resultado = $service->cancelar(['id' => 3]);

        $this->assertSame('Cancelada', $resultado->getEstado());
    }
}
