<?php

namespace Database\Seeders;

use App\Models\Corporate;
use App\Models\CorporateGroup;
use Illuminate\Database\Seeder;

/**
 * Municípios que entram na atualização de bancos DEV/HMG: marca como ativos, vincula ao grupo (estado,
 * criado se não existir) e preenche hosts e bancos.
 * Padrão: host {municipio}[dev|hmg].servidorcaju.com.br e banco {municipio}_[prod|dev|hmg].
 * As demais organizações ficam com ativo = false (default da coluna).
 */
class CorporatesBancosSeeder extends Seeder
{
    // sigla da organização (corporates.initials) => [nome do município nos hosts e bancos, grupo]
    const MUNICIPIOS = [
        'BRA' => ['brasileira', 'PIAUÍ'],
        'BDM' => ['buritidosmontes', 'PIAUÍ'],
        'CJP' => ['cajueirodapraia', 'PIAUÍ'],
        'CPL' => ['campolargo', 'PIAUÍ'],
        'COC' => ['cocal', 'PIAUÍ'],
        'GUA' => ['guaribas', 'PIAUÍ'],
        'LCO' => ['luiscorreia', 'PIAUÍ'],
        'MCT' => ['morrocabecanotempo', 'PIAUÍ'],
        'NSR' => ['nossasenhoradosremedios', 'PIAUÍ'],
        'NVO' => ['novooriente', 'PIAUÍ'],
        'VRM' => ['veramendes', 'PIAUÍ'],
        'STM' => ['santana', 'MARANHÃO'],
        'SDB' => ['saobernardo', 'MARANHÃO'],
        'QTP' => ['quiterianopolis', 'CEARÁ'],
    ];

    public function run()
    {
        foreach (self::MUNICIPIOS as $sigla => [$municipio, $grupo]) {
            $atualizadas = Corporate::where('initials', $sigla)->update([
                'ativo' => true,
                'corporate_group_id' => CorporateGroup::firstOrCreate(['nome' => $grupo])->id,
                'host_prod' => "$municipio.servidorcaju.com.br",
                'banco_prod' => "{$municipio}_prod",
                'host_dev' => "{$municipio}dev.servidorcaju.com.br",
                'banco_dev' => "{$municipio}_dev",
                'host_hmg' => "{$municipio}hmg.servidorcaju.com.br",
                'banco_hmg' => "{$municipio}_hmg",
            ]);

            if ($atualizadas !== 1) {
                $this->command?->warn("Sigla $sigla ($municipio): $atualizadas organizações encontradas, esperado 1.");
            }
        }
    }
}
