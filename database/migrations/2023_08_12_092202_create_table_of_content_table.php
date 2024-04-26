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
        Schema::create('table_of_content', function (Blueprint $table) {
            // Ex : Schema::create('publication_title', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('level', 2,1)->default(0);
            $table->unsignedInteger('order')->default(1);
            $table->string('start_page',8)->nullable();
            $table->string('end_page',8)->nullable();

            $table->unsignedInteger('publication_id');
            $table->foreign('publication_id')
                ->references('id')
                ->on('publications')
                ->onDelete('restrict');

            $table->unsignedInteger('title_id');
            $table->foreign('title_id')
                ->references('id')
                ->on('titles')
                ->onDelete('restrict');

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
        Schema::dropIfExists('table_of_content');
    }
};
