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
}
