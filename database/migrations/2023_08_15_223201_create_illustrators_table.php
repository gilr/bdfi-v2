<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\AuthorGender;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('illustrators', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name', 128);
            $table->string('first_name', 32)->nullable();
            $table->string('legal_name', 128)->nullable();
            $table->string('alt_names', 512)->nullable();
            $table->string('gender')->default(AuthorGender::INCONNU->value); // Enum AuthorGender['F', 'H', 'IEL', '?'])

            $table->unsignedInteger('author_id')->nullable();
            $table->foreign('author_id')
                ->references('id')
                ->on('authors')
                ->onDelete('restrict');

            $table->text('information')->nullable(); // Biographie
            $table->text('private')->nullable();

            $table->timestamps();
            $table->unsignedSmallInteger('created_by')->nullable();
            $table->unsignedSmallInteger('updated_by')->nullable();
            $table->unsignedSmallInteger('deleted_by')->nullable();
            $table->softdeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('illustrators');
    }
};
