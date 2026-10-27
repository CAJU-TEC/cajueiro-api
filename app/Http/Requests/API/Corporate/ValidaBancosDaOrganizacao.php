<?php

namespace App\Http\Requests\API\Corporate;

use Illuminate\Validation\Rule;

/**
 * Regras dos campos de host e banco da organização, usados na atualização de DEV/HMG a partir de PROD.
 */
trait ValidaBancosDaOrganizacao
{
    public function regrasBancos(): array
    {
        $host = ['nullable', 'string', 'max:125'];
        $banco = [...$host, 'regex:/^[A-Za-z0-9_]+$/'];

        return [
            'ativo' => 'sometimes|boolean',
            'corporate_group_id' => ['nullable', Rule::exists('corporate_groups', 'id')->whereNull('deleted_at')],
            'host_prod' => $host,
            'banco_prod' => $banco,
            'host_dev' => $host,
            'banco_dev' => $banco,
            'host_hmg' => $host,
            'banco_hmg' => $banco,
        ];
    }

    public function nomesBancos(): array
    {
        return [
            'corporate_group_id' => 'grupo',
            'host_prod' => 'host de produção',
            'banco_prod' => 'banco de produção',
            'host_dev' => 'host de desenvolvimento',
            'banco_dev' => 'banco de desenvolvimento',
            'host_hmg' => 'host de homologação',
            'banco_hmg' => 'banco de homologação',
        ];
    }

    public function mensagensBancos(): array
    {
        return ['*.regex' => 'Use só letras, números e _ no nome do banco.'];
    }

    public function withValidator($validator)
    {
        // DEV/HMG no servidor de produção significaria apagar produção.
        $validator->after(function ($validator) {
            foreach (['dev', 'hmg'] as $destino) {
                $host = trim((string) $this->input("host_$destino"));

                if ($host !== '' && strcasecmp($host, trim((string) $this->input('host_prod'))) === 0) {
                    $validator->errors()->add("host_$destino", 'O host de DEV/HMG não pode ser o mesmo de produção.');
                }
            }
        });
    }
}
