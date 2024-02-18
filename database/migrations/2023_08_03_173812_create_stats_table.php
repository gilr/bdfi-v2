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
        Schema::create('stats', function (Blueprint $table) {
            $table->increments('id');

            $table->date('date');
            $table->unsignedInteger('authors');
            $table->unsignedInteger('series')->nullable();
            $table->unsignedInteger('references');
            $table->unsignedInteger('novels');
            $table->unsignedInteger('short_stories');
            $table->unsignedInteger('collections')->nullable();
            $table->unsignedInteger('magazines')->nullable();
            $table->unsignedInteger('essays')->nullable();

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
        Schema::dropIfExists('stats');
    }
};
