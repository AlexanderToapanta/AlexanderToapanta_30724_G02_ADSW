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

}
