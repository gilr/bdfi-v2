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
        Schema::create('reprints', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 256)->nullable();

            $table->unsignedInteger('publication_id')->nullable()->default(NULL);
            $table->foreign('publication_id')
                ->references('id')
                ->on('publications')
                ->onDelete('restrict');

            $table->string('ai', 10)->nullable();
            $table->string('approximate_parution', 10)->nullable();
            $table->boolean('is_verified');
            $table->string('verified_by', 256)->nullable();
            $table->text('information')->nullable();
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
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reprints');
    }
};
