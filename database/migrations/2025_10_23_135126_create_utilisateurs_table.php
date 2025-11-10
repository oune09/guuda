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
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('nom_utilisateur');
            $table->string('prenom_utilisateur');
            $table->string('email_utilisateur')->unique();
            $table->string('cnib');
            $table->string('mot_de_passe_utilisateur');
            $table->date('date_naissance_utilisateur');
            $table->string('telephone_utilisateur')->unique();
            $table->string('photo')->nullable(); 
            $table->enum('role_utilisateur',['citoyen','autorite','administrateur'])->default('citoyen');
            $table->string('ville');
            $table->string('secteur');
            $table->string('quartier');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
