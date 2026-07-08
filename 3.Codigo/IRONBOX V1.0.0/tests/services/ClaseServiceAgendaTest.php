<?php

use PHPUnit\Framework\TestCase;

final class ClaseServiceAgendaTest extends TestCase
{
    private function datosClaseValida(): array
    {
        return [
            'dia' => date('Y-m-d', strtotime('+7 days')),
            'hora' => '10:00',
            'duracion' => 60,
            'cupoMaximo' => 20,
            'entrenadorId' => 5,
        ];
    }

    public function test_eliminar_clase_existente(): void
    {
        $claseDaoMock = $this->createMock(ClaseDAO::class);
        $claseDaoMock->expects($this->once())->method('eliminar')->with(1)->willReturn(true);

        $membresiaDaoMock = $this->createMock(MembresiaDAO::class);

        $service = new ClaseService($claseDaoMock, $membresiaDaoMock);
        $service->eliminar(1);

        $this->assertTrue(true);
    }

    public function test_eliminar_clase_inexistente_lanza_error(): void
    {
        $claseDaoMock = $this->createMock(ClaseDAO::class);
        $claseDaoMock->method('eliminar')->with(99)->willReturn(false);

        $membresiaDaoMock = $this->createMock(MembresiaDAO::class);

        $service = new ClaseService($claseDaoMock, $membresiaDaoMock);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('La clase solicitada no existe o ya fue eliminada.');
        $service->eliminar(99);
    }

    public function test_crear_clase_falla_si_entrenador_no_existe_o_no_disponible(): void
    {
        $claseDaoMock = $this->createMock(ClaseDAO::class);
        $claseDaoMock->method('entrenadorExisteYDisponible')->with(5)->willReturn(false);

        $membresiaDaoMock = $this->createMock(MembresiaDAO::class);

        $service = new ClaseService($claseDaoMock, $membresiaDaoMock);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('El entrenador asignado no existe o no esta disponible.');
        $service->crear($this->datosClaseValida());
    }

    public function test_crear_clase_falla_si_hay_solapamiento_del_entrenador(): void
    {
        $claseDaoMock = $this->createMock(ClaseDAO::class);
        $claseDaoMock->method('entrenadorExisteYDisponible')->willReturn(true);
        $claseDaoMock->method('existeSolapamientoEntrenador')->willReturn(true);

        $membresiaDaoMock = $this->createMock(MembresiaDAO::class);

        $service = new ClaseService($claseDaoMock, $membresiaDaoMock);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('El entrenador asignado no esta disponible en ese horario.');
        $service->crear($this->datosClaseValida());
    }

    public function test_crear_clase_falla_si_hay_solapamiento_general(): void
    {
        $claseDaoMock = $this->createMock(ClaseDAO::class);
        $claseDaoMock->method('entrenadorExisteYDisponible')->willReturn(true);
        $claseDaoMock->method('existeSolapamientoEntrenador')->willReturn(false);
        $claseDaoMock->method('existeSolapamientoGeneral')->willReturn(true);

        $membresiaDaoMock = $this->createMock(MembresiaDAO::class);

        $service = new ClaseService($claseDaoMock, $membresiaDaoMock);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('El horario se solapa con otra clase existente.');
        $service->crear($this->datosClaseValida());
    }

    public function test_editar_clase_ignora_su_propio_id_al_validar_solapamiento(): void
    {
        $datos = $this->datosClaseValida();
        $claseExistente = new Clase(
            7,
            $datos['dia'],
            $datos['hora'],
            $datos['duracion'],
            $datos['cupoMaximo'],
            $datos['cupoMaximo'],
            $datos['entrenadorId']
        );

        $claseDaoMock = $this->createMock(ClaseDAO::class);
        $claseDaoMock->method('buscarPorId')->with(7)->willReturn($claseExistente);
        $claseDaoMock->method('entrenadorExisteYDisponible')->willReturn(true);
        $claseDaoMock->expects($this->once())
            ->method('existeSolapamientoEntrenador')
            ->with($datos['dia'], $datos['hora'], $datos['duracion'], $datos['entrenadorId'], 7)
            ->willReturn(false);
        $claseDaoMock->expects($this->once())
            ->method('existeSolapamientoGeneral')
            ->with($datos['dia'], $datos['hora'], $datos['duracion'], 7)
            ->willReturn(false);
        $claseDaoMock->method('actualizar')->willReturnArgument(0);

        $membresiaDaoMock = $this->createMock(MembresiaDAO::class);

        $service = new ClaseService($claseDaoMock, $membresiaDaoMock);
        $resultado = $service->editar(7, $datos);

        $this->assertSame(7, $resultado->getId());
    }
}
