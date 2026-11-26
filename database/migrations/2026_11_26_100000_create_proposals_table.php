<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Propositions de contributeurs (ajout d'un ouvrage, puis corrections, sommaires...), stockées au format JSON
 * dans une table à part : rien n'entre dans les tables "normales" avant l'acceptation par un membre.
 * Script SQL équivalent (production, sans artisan) : database/sql/2026_11_proposals.sql - les deux doivent rester identiques.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedBigInteger('user_id');   // le proposant
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');

            // Voir enums ProposalType et ProposalStatus
            $table->string('type', 32);
            $table->string('status', 16)->default('submitted');

            // Cible d'une correction (ouvrage, éditeur, collection...) : vide pour un ajout
            $table->string('target_type', 32)->nullable();
            $table->unsignedInteger('target_id')->nullable();

            // Extraits de la proposition, pour lister, trier et repérer les doublons sans lire le JSON
            $table->string('title', 128);
            $table->string('publisher_name', 128)->nullable();
            $table->string('approximate_parution', 10)->nullable();
            $table->string('isbn', 18)->nullable();

            // La proposition elle-même : pour un ajout d'ouvrage, un livre au format JSON canonique de l'import
            $table->json('payload');

            // Étude par un membre
            $table->text('review_note')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedInteger('result_id')->nullable();   // id de l'élément créé ou corrigé

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
