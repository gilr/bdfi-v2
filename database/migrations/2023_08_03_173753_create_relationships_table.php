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
        Schema::create('relationships', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('author1_id');
            $table->foreign('author1_id')
                ->references('id')
                ->on('authors')
                ->onDelete('restrict');

            $table->unsignedInteger('author2_id');
            $table->foreign('author2_id')
                ->references('id')
                ->on('authors')
                ->onDelete('restrict');

            $table->unsignedTinyInteger('relationship_type_id');
            $table->foreign('relationship_type_id')
                ->references('id')
                ->on('relationship_types')
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
        Schema::dropIfExists('relationships');
    }
};
