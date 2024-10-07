<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\AuthorGender;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name', 32);
            $table->string('first_name', 32)->nullable();
            $table->string('slug', 64)->nullable();

            $table->string('nom_bdfi', 64)->nullable();
            $table->string('legal_name', 128)->nullable();
            $table->string('alt_names', 512)->nullable();
            $table->boolean('is_pseudonym');

            $table->string('gender')->default(AuthorGender::INCONNU->value); // Enum AuthorGender['F', 'H', 'IEL', '?'])

            $table->unsignedSmallInteger('country_id')->nullable();
            $table->foreign('country_id')
                ->references('id')
                ->on('countries')
                ->onDelete('restrict');

            $table->unsignedSmallInteger('country2_id')->nullable();
            $table->foreign('country2_id')
                ->references('id')
                ->on('countries')
                ->onDelete('restrict');

            $table->string('birth_date', 10)->nullable();
            $table->string('birthplace', 64)->nullable();
            $table->string('date_death', 10)->nullable();
            $table->string('place_death', 64)->nullable();

            $table->boolean('is_visible');
            $table->text('information')->nullable(); // Biographie
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
        Schema::dropIfExists('authors');
    }
};
