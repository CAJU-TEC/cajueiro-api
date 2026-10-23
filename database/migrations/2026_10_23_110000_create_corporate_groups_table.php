<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Grupos de organizações (ex.: municípios de um mesmo estado), só para separar na visualização.
     */
    public function up()
    {
        Schema::create('corporate_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome');
            $table->timestamps();
            $table->softDeletes();
        });

        // sem FK: corporates.id não tem primary key neste schema, e o grupo é só um agrupamento visual
        Schema::table('corporates', function (Blueprint $table) {
            $table->uuid('corporate_group_id')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('corporates', function (Blueprint $table) {
            $table->dropColumn('corporate_group_id');
        });

        Schema::dropIfExists('corporate_groups');
    }
};
