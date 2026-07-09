<?php

use PHPUnit\Framework\TestCase;

final class MembresiaServiceAsignacionTest extends TestCase
{
    private function membresiaBase(int $id = 1, int $idAtleta = 20): Membresia
    {
        return new Membresia(
            $id,
            'Mensual',
            50.0,
            '2026-02-01',
            '2026-03-03',
            'Pendiente',
            $idAtleta
        );
    }

    public function test_crea_membresia_para_atleta_existente(): void
    {
        $daoMock = $this->createMock(MembresiaDAO::class);
        $daoMock->method('atletaExiste')->with(20)->willReturn(true);
        $daoMock->method('crear')->willReturnArgument(0);

        $service = new MembresiaService($daoMock);
        $resultado = $service->crear([
            'idAtleta' => 20,
            'tipo' => 'Mensual',
            'precio' => 50,
            'fechaInicio' => '2026-02-01',
            'estado' => 'Pendiente',
        ]);

        $this->assertSame(20, $resultado->getIdAtleta());
        $this->assertSame('Mensual', $resultado->getTipo());
        $this->assertSame(50.0, $resultado->getPrecio());
    }

    public function test_crear_falla_si_atleta_no_existe(): void
    {
        $daoMock = $this->createMock(MembresiaDAO::class);
        $daoMock->method('atletaExiste')->with(999)->willReturn(false);

        $service = new MembresiaService($daoMock);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('El atleta seleccionado no existe.');
        $service->crear([
            'idAtleta' => 999,
            'tipo' => 'Mensual',
            'precio' => 50,
            'fechaInicio' => '2026-02-01',
            'estado' => 'Pendiente',
        ]);
    }

}
