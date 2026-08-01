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
        Schema::table('job_plans', function (Blueprint $table) {
            $table->uuid('team_id')->nullable()->after('id');
            $table->string('badge_icon')->nullable()->after('color');
            $table->string('badge_color')->nullable()->after('badge_icon');
            $table->integer('position')->default(0)->after('badge_color');
            $table->index('team_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_plans', function (Blueprint $table) {
            $table->dropIndex(['team_id']);
            $table->dropColumn(['team_id', 'badge_icon', 'badge_color', 'position']);
        });
    }
};
