<?php

namespace Tests\Unit;

use App\Models\Corporate;
use App\Actions\Dumps\ValidarDestinoAction;
use InvalidArgumentException;
use Illuminate\Foundation\Testing\TestCase;
use Tests\CreatesApplication;

class ValidarDestinoActionTest extends TestCase
{
    use CreatesApplication; // sem RefreshDatabase: não precisa de banco

    private function organizacao(array $over = []): Corporate
    {
        return new Corporate($over + [
            'ativo' => true,
            'host_prod' => 'prod.local', 'banco_prod' => 'x_prod',
            'host_dev' => 'dev.local', 'banco_dev' => 'x_dev',
            'host_hmg' => 'hmg.local', 'banco_hmg' => 'x_hmg',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['prod', 'dev', 'hmg'] as $a) {
            config(["db_refresh.{$a}.user" => 'u']);
        }
    }

    public function test_destino_valido_passa()
    {
        (new ValidarDestinoAction)($this->organizacao(), 'dev');
        $this->assertTrue(true);
    }

    public function test_prod_nunca_e_destino()
    {
        $this->expectException(InvalidArgumentException::class);
        (new ValidarDestinoAction)($this->organizacao(), 'prod');
    }

    public function test_host_destino_igual_prod_e_recusado()
    {
        $this->expectException(InvalidArgumentException::class);
        (new ValidarDestinoAction)($this->organizacao(['host_dev' => 'PROD.local']), 'dev');
    }

    public function test_organizacao_inativa_ou_sem_banco_e_recusada()
    {
        $this->expectException(InvalidArgumentException::class);
        (new ValidarDestinoAction)($this->organizacao(['ativo' => false]), 'dev');
    }

    public function test_nome_de_banco_invalido_e_recusado()
    {
        $this->expectException(InvalidArgumentException::class);
        (new ValidarDestinoAction)($this->organizacao(['banco_hmg' => 'x; DROP DATABASE y']), 'hmg');
    }
}
