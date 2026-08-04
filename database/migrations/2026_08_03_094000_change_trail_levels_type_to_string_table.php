<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * `type` deixa de ser enum de banco e passa a varchar.
 *
 * Com a separação soft/hard vieram tipos próprios de soft skill (mentoria,
 * apresentação, dinâmica, leitura), e enum de banco obriga uma migration a
 * cada tipo novo. A lista válida passa a viver na FormRequest, que já era
 * quem conferia isso na entrada.
 */
return new class extends Migration
{
    private const ENUM = ['task', 'course', 'platform', 'technical_test', 'other'];

    /**
     * SQL cru e só para MySQL de propósito.
     *
     * O caminho portátil seria `->change()`, mas este projeto tem
     * doctrine/dbal ^4.1 com Laravel 9, e as duas versões são incompatíveis na
     * alteração de coluna: qualquer `change()`, `renameColumn()` ou
     * `dropColumn()` em SQLite estoura em ConnectsToDatabase::connect(). O
     * phpunit.xml aponta para MySQL, que é onde a suíte roda.
     */
    public function up()
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE trail_levels MODIFY COLUMN type VARCHAR(32) NOT NULL DEFAULT 'task'");
    }

    public function down()
    {
        // Volta para o enum antigo. Tipo fora da lista original vira 'other',
        // senão o ALTER falha nas linhas que não cabem mais.
        DB::table('trail_levels')->whereNotIn('type', self::ENUM)->update(['type' => 'other']);

        if (DB::getDriverName() === 'mysql') {
            $values = "'" . implode("','", self::ENUM) . "'";
            DB::statement("ALTER TABLE trail_levels MODIFY COLUMN type ENUM($values) NOT NULL DEFAULT 'task'");
        }
    }
};
