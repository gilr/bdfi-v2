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
        Schema::create('cycle_title', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number',16)->nullable;
            $table->unsigneddecimal('order', 5,2)->default(0);

            $table->unsignedInteger('cycle_id');
            $table->foreign('cycle_id')
                ->references('id')
                ->on('cycles')
                ->onDelete('restrict');

            $table->unsignedInteger('title_id');
            $table->foreign('title_id')
                ->references('id')
                ->on('titles')
                ->onDelete('restrict');

            $table->timestamps();
            $table->unsignedSmallInteger('created_by')->nullable();
            $table->unsignedSmallInteger('updated_by')->nullable();
            $table->unsignedSmallInteger('deleted_by')->nullable();
            $table->softdeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cycle_title');
    }
};
