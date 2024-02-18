<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\PublisherType;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('publishers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 128);
            $table->string('sigle_bdfi', 8)->nullable();
            $table->string('alt_names', 512)->nullable();

            $table->unsignedSmallInteger('country_id')->nullable();
            $table->foreign('country_id')
                ->references('id')
                ->on('countries')
                ->onDelete('restrict');

            $table->string('type')->default(PublisherType::EDITEUR->value); // Enum PublisherType ['editeur', 'microediteur', 'autoediteur', 'compte_auteur', 'autre']

            $table->unsignedInteger('year_start');
            $table->unsignedInteger('year_end')->nullable();
            $table->text('address')->nullable();

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
        Schema::dropIfExists('publishers');
    }
};
