<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('check_list_collaborator')) {
            return;
        }
        Schema::create('check_list_collaborator', function (Blueprint $table) {
            $table->uuid('check_list_id')->nullable();
            $table->uuid('collaborator_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('check_list_collaborator');
    }
};
