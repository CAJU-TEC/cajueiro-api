<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Auditoria (pacote owen-it/laravel-auditing), usada pelos models Auditable (hoje Ticket).
     * Sem chave estrangeira de propósito: o histórico precisa sobreviver à exclusão do registro e do usuário.
     */
    public function up()
    {
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $table = config('audit.drivers.database.table', 'audits');

        Schema::connection($connection)->create($table, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_name')->nullable(); // quem fez (nome na época, mesmo que o usuário seja renomeado/apagado)
            $table->string('event', 30)->index(); // created, updated, deleted, restored, split, split_created...
            $table->uuidMorphs('auditable'); // auditable_type + auditable_id
            $table->unsignedInteger('ticket_code')->nullable()->index(); // número legível do protocolo
            $table->text('summary')->nullable(); // frase legível do que mudou
            $table->json('old_values')->nullable(); // valores antes (só os campos alterados)
            $table->json('new_values')->nullable(); // valores depois
            $table->mediumText('message_before')->nullable(); // descrição antes da edição
            $table->mediumText('message_after')->nullable(); // descrição depois da edição
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down()
    {
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $table = config('audit.drivers.database.table', 'audits');

        Schema::connection($connection)->drop($table);
    }
};
