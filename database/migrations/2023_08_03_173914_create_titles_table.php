<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\IsNovelization;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('titles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 256);
            // subtitle ? Je dirai non, mais "Titre - sous-titre" comme quasi normalisé

            $table->string('type'); // Enum TitleType

            $table->unsignedInteger('parent_id')->nullable();
            $table->foreign('parent_id')
                ->references('id')
                ->on('titles')
                ->onDelete('restrict');
            $table->string('variant_type'); // Enum TitleVariantType

            $table->string('copyright', 10)->nullable();
            $table->string('is_novelization')->default(IsNovelization::NON->value);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_serial')->default(false);
            $table->boolean('is_fullserial')->default(false); // TBD TODO temporaire si ensuite via une table
            $table->string('serial_info', 512);

            $table->text('synopsis')->nullable();

            $table->string('title_vo', 512)->nullable(); // format "titre 1 / variant title" si c'est le VT qui est traduit
            $table->string('copyright_fr', 10)->nullable();
            $table->string('pub_vo', 256)->nullable();

            $table->string('translators', 512)->nullable();; // format "nom prénom, nom prénom", etc si plusieurs
            // Question : les titres Vo ne sont stockés qu'ici, pas dans une table annexe
            // Implique qu'on ne gère pas les variantes de titre VO directement, mais indirectement de ceux traduits,
            // au travers des variants de titres fr.
            // => "variant fr" si titre revu, si texte révisé, si autre signature (pseu -> auteur),
            // ... ou si pour une traduction le texte d'origine a été révisé, ou si autre traducteur
            // Attention, ce sujet variants est sans doute le plus compliqué à gérer (exemple avec "Le Horla" quand on ne connait pas si v1 ou v2)

            $table->text('information')->nullable();
            $table->text('private')->nullable();

            // Genre - thésaurus : à revoir
            $table->string('is_genre'); // Enum GenreAppartenance ['yes', 'partial', 'no']
            $table->string('genre_stat'); // Enum GenreStat ['sf', 'fantasy', 'fantastic', 'hybrid', 'other', 'mainstream']
            $table->string('target_audience'); // Enum AudienceTarget
            $table->string('target_age')->nullable();

/*
    A voir dans un thesaurus ? Conserver temporairement 2 genres ?
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
        Schema::dropIfExists('titles');
    }
};
