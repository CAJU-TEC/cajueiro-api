<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * --
     * @return void
     */
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            if (!Schema::hasColumn('comments', 'recorrencia')) {
                $table->boolean('recorrencia')->default(false)->after('testing');
            }
        });
    }

    /**
     * Reverse the migrations.
     * --
     * @return void
     */
    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('recorrencia');
        });
    }
};
