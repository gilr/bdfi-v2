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
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name', 128);
            $table->string('type'); // Enum EventType ['convention','festival','exposition','salon','film-festival','autre']
            $table->date('start_date');
            $table->date('end_date');
            $table->string('place', 64);
            $table->text('information');
            $table->string('url', 256)->nullable();

            $table->boolean('is_confirmed');
            $table->boolean('is_full_scope');
            $table->datetime('publication_date')->nullable();

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
        Schema::dropIfExists('events');
    }
};
