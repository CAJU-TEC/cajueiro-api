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
        Schema::create('trail_materials', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('description');
            $table->string('url');
            $table->enum('type', [
                'course',
                'platform',
                'technical_test',
                'documentation',
                'other',
            ])->default('other');
            $table->uuid('materialable_id')->nullable();
            $table->string('materialable_type')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['materialable_id', 'materialable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trail_materials');
    }
};
