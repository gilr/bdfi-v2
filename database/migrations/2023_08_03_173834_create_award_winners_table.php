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
        Schema::create('award_winners', function (Blueprint $table) {
            $table->increments('id');
            $table->year('year');

            $table->unsignedInteger('award_category_id');
            $table->foreign('award_category_id')
                ->references('id')
                ->on('award_categories')
                ->onDelete('restrict');

            $table->unsignedTinyInteger('position');
            $table->string('name', 256)->nullable();

            $table->unsignedInteger('author_id')->nullable();
            $table->foreign('author_id')
                ->references('id')
                ->on('authors')
                ->onDelete('restrict');
            $table->unsignedInteger('author2_id')->nullable();
            $table->foreign('author2_id')
                ->references('id')
                ->on('authors')
                ->onDelete('restrict');

            $table->unsignedInteger('author3_id')->nullable();
            $table->foreign('author3_id')
                ->references('id')
                ->on('authors')
                ->onDelete('restrict');

            $table->string('title', 256)->nullable();
            $table->string('vo_title', 256)->nullable();

            $table->unsignedInteger('title_id')->nullable();
/*            $table->foreign('title_id')
                ->references('id')
                ->on('titles')
                ->onDelete('restrict'); */

            $table->text('information')->nullable();

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
        Schema::dropIfExists('award_winners');
    }
};
