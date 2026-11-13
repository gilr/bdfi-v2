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
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name', 128);
            $table->string('slug', 128)->nullable();

            $table->string('type'); // Enum EventType ['convention','festival','exposition','salon','film-festival','autre']
            $table->string('periodicity')->nullable(); // Enum EventPeriodicity ['unique','annuel','biennal','irregulier']
            $table->string('year_start', 4)->nullable(); // Année de la première occurrence
            $table->string('year_end', 4)->nullable();   // Année de la dernière occurrence, vide si toujours actif
            // Obsolètes (transition) : remplacés par event_occurrences, à supprimer en phase 3
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            // Lieu habituel, précisé ou remplacé par event_occurrences.place
            $table->string('place', 64)->nullable();
            $table->text('information')->nullable();
            $table->string('url', 256)->nullable();

            // Obsolète (transition) : remplacé par event_occurrences.is_confirmed, à supprimer en phase 3
            $table->boolean('is_confirmed')->nullable();
            $table->boolean('is_full_scope');
            // Obsolète (transition) : remplacé par event_occurrences.publication_date, à supprimer en phase 3
            $table->datetime('publication_date')->nullable();

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
        Schema::dropIfExists('events');
    }
};
