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
        Schema::create('documents', function (Blueprint $table) {
            $table->increments('id');

            $table->text('name');
            $table->text('file');

            $table->unsignedInteger('author_id')->nullable()->default(NULL);
            $table->foreign('author_id')
                ->references('id')
                ->on('authors')
                ->onDelete('restrict');

            // articleable_id - integer
            // articleable_type - string
            $table->string('item_type');
            $table->unsignedInteger('item_id');

            $table->timestamps();
            $table->unsignedSmallInteger('created_by')->nullable();
            $table->unsignedSmallInteger('updated_by')->nullable();
            $table->unsignedSmallInteger('deleted_by')->nullable();
            $table->softdeletes();        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
