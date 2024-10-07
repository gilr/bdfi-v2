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
        Schema::create('award_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 128);
            $table->string('slug', 256)->nullable();

            $table->unsignedInteger('award_id');
            $table->foreign('award_id')
                ->references('id')
                ->on('awards')
                ->onDelete('restrict');

            $table->unsignedInteger('internal_order')->nullable();
            $table->string('type'); // Enum AwardCategoryType ['auteur', 'roman', 'novella', 'nouvelle', 'texte', 'anthologie', 'recueil', 'special']
            $table->string('genre'); // Enum AwardCategoryGenre ['sf', 'fantastique', 'fantasy', 'horreur', 'imaginaire', 'mainstream']
            $table->string('subgenre', 256)->nullable();

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
        Schema::dropIfExists('award_categories');
    }
};
