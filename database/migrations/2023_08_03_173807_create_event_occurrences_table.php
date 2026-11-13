<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Occurrences (éditions) d'un évènement de la table 'events'.
 * En production (mutualisé, pas d'artisan), la table est créée par le script
 * database/sql/2026_11_events_phase1.sql : garder les deux strictement identiques.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_occurrences', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('event_id');
            $table->foreign('event_id')
                ->references('id')
                ->on('events')
                ->onDelete('restrict');

            $table->string('label', 128)->nullable(); // Facultatif ("24e édition"...) ; à défaut, l'année sert d'identifiant
            $table->date('start_date');
            $table->date('end_date');
            $table->string('place', 128)->nullable(); // Précise ou remplace events.place
            $table->text('information')->nullable();

            $table->boolean('is_confirmed')->default(false);
            $table->datetime('publication_date')->nullable();

            $table->timestamps();
            $table->unsignedSmallInteger('created_by')->nullable();
            $table->unsignedSmallInteger('updated_by')->nullable();
            $table->unsignedSmallInteger('deleted_by')->nullable();
            $table->softdeletes();

            $table->index('start_date');
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_occurrences');
    }
};
