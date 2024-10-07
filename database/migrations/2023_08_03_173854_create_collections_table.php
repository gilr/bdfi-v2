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
        Schema::create('collections', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 128);
            $table->string('slug', 128)->nullable();

            $table->string('shortname', 128);
            $table->string('type'); // collection, ensemble, revue, fanzine, magazine, journal, antho-p
            $table->string('periodicity'); // na, quotidien, hebdo, bimensuel, mensuel, ...
            $table->string('sigle_bdfi', 8)->nullable();
            $table->string('alt_names', 512)->nullable();

            $table->unsignedInteger('publisher_id')->nullable();
            $table->foreign('publisher_id')
                ->references('id')
                ->on('publishers')
                ->onDelete('restrict');

            $table->unsignedInteger('publisher2_id')->nullable();
            $table->foreign('publisher2_id')
                ->references('id')
                ->on('publishers')
                ->onDelete('restrict');

            $table->unsignedInteger('publisher3_id')->nullable();
            $table->foreign('publisher3_id')
                ->references('id')
                ->on('publishers')
                ->onDelete('restrict');

            $table->unsignedInteger('parent_id')->nullable();
            $table->foreign('parent_id')
                ->references('id')
                ->on('collections')
                ->onDelete('restrict');

            $table->unsignedInteger('year_start');
            $table->unsignedInteger('year_end')->nullable();

            $table->string('support'); // ['papier', 'numerique', 'audio', 'mixte', 'autre']
            $table->string('format')->nullable(); // ['poche', 'moyen', 'grand', 'mixte'])
            $table->string('dimensions', 10)->nullable();

            // A transformer en morph2many vers des tags cible et genre
            $table->string('cible')->nullable(); // ['jeunesse', 'YA', 'adulte']
            $table->string('genre')->nullable(); // ['sf', 'fantasy', 'fantastique','gore','policier']

            $table->unsignedInteger('forum_topic_id')->nullable();

            $table->text('information')->nullable();
            $table->text('private')->nullable();
            $table->string('quality'); // Enum QualityStatus

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
        Schema::dropIfExists('collections');
    }
};
