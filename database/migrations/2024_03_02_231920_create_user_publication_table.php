<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_publication', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');

            $table->unsignedInteger('publication_id');
            $table->foreign('publication_id')
                ->references('id')
                ->on('publications')
                ->onDelete('restrict');

            // Voir enum BiblioPublicationStatus ['possede', 'abime'] (script SQL : database/sql/2026_11_user_publication_status.sql)
            $table->string('status')->default('possede');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_publication');
    }
};
