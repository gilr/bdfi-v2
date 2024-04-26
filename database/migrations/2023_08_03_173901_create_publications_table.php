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
        Schema::create('publications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 128);
            $table->string('status')->default('paru');
                // Enum PublicationStatus PUBLIE ('paru'), ANNONCE ('annonce'), PROPOSE ('proposal'), ABANDONNE ('abandon')

            $table->string('cycle', 128)->nullable();
            $table->string('cyclenum', 10)->nullable();

            $table->unsignedInteger('publisher_id')->nullable()->default(NULL);
            $table->foreign('publisher_id')
                ->references('id')
                ->on('publishers')
                ->onDelete('restrict');
            $table->string('publisher_name', 128)->nullable(); // Surcharge pour si renommage

            $table->boolean('is_visible')->default(true);

            $table->string('isbn', 18)->nullable();
            $table->string('cover', 256)->nullable();
            $table->string('illustrators', 512)->nullable();
            $table->text('information')->nullable();
            $table->text('private')->nullable();

            $table->string('cover_front', 64)->nullable();
            $table->string('cover_back', 64)->nullable();
            $table->string('cover_spine', 64)->nullable();
            $table->string('withband_front', 64)->nullable();
            $table->string('withband_back', 64)->nullable();
            $table->string('withband_spine', 64)->nullable();
            $table->string('dustjacket_front', 64)->nullable();
            $table->string('dustjacket_back', 64)->nullable();
            $table->string('dustjacket_spine', 64)->nullable();

            $table->boolean('is_hardcover')->default(false);
            $table->boolean('has_dustjacket')->default(false);
            $table->boolean('has_coverflaps')->default(false);

            $table->boolean('is_verified');
            $table->string('verified_by', 256)->nullable();
            $table->string('dl', 10)->nullable();
            $table->string('ai', 10)->nullable();
            $table->string('edition', 64)->nullable();
            $table->string('dimensions', 10)->nullable();
            $table->string('thickness', 4)->nullable();
            $table->string('printer', 128)->nullable();
            $table->string('printed_price', 32)->nullable();
            $table->string('pagination', 32)->nullable();
            $table->string('pages_dpi')->nullable();
            $table->string('pages_dpu')->nullable();

            // Données indicatives :
            $table->string('approximate_pages')->nullable();
            $table->string('approximate_parution', 10)->nullable();
            $table->string('approximate_price', 32)->nullable();

            $table->string('support'); // Enum PublicationSupport ['papier', 'numerique', 'audio', 'autre']
            $table->string('format')->nullable(); // Enum PublicationFormat ['poche', 'moyen format', 'grand format', 'autre', 'n/a', 'inconnu']
            $table->string('type'); // Enum PublicationType // ['fiction', 'assemblage', 'omnibus', 'revue', 'non-fiction']

            // Genre - thésaurus : à revoir
            $table->string('is_genre'); // Enum GenreAppartenance ['yes', 'partial', 'no']
            $table->string('genre_stat'); // Enum GenreStat ['sf', 'fantasy', 'fantastic', 'hybrid', 'other', 'mainstream']
            $table->string('target_audience'); // Enum AudienceTarget
            $table->string('target_age')->nullable();


/*
            $table->enum('if_other', ['prehistoric', 'gore', 'etrange', 'peur', 'horreur', 'n/a']);

            $table->enum('theme1', ['SF', 'fantastique', 'fantasy', 'conte-de-fee', 'hybride', 'mythologie', 'prehistoric', 'gore', 'etrange', 'peur', 'horreur', 'realisme-magique']);
            $table->enum('theme2', ['aventure', 'chevalerie', 'erotique', 'espionnage', 'guerre', 'historique', 'humour', 'peur', 'policier', 'porno', 'romance', 'thriller', 'western']);
*/
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
        Schema::dropIfExists('publications');
    }
};
