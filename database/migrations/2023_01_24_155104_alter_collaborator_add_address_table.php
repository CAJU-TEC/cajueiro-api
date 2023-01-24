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
        //
        Schema::table('collaborators', function (Blueprint $table) {
            $table->string('address')->nullable()->after('formation');
            $table->string('postal')->nullable()->after('formation');
            $table->string('number')->nullable()->after('formation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('collaborators', function (Blueprint $table) {
            $table->dropColumn('address');
            $table->dropColumn('postal');
            $table->dropColumn('number');
        });
    }
};
