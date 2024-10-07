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
        Schema::create('cycles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 128);
            $table->string('slug', 128)->nullable();

            $table->string('nom_bdfi', 128)->nullable();
            $table->string('alt_names', 512)->nullable();
            $table->string('vo_names', 256)->nullable();

            $table->string('type'); // Voir enum CycleType ['serie', 'cycle', 'univers', 'feuilleton', 'autre']

            $table->unsignedInteger('parent_id')->nullable();
            $table->foreign('parent_id')
                ->references('id')
                ->on('cycles')
                ->onDelete('restrict');

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
        Schema::dropIfExists('cycles');
    }
};
