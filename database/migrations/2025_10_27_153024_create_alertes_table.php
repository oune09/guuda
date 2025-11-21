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
        Schema::create('alertes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('autorite_id')->constrained('autorites')->onDelete('cascade');
            $table->foreignId('incident_id')->constrained('incidents')->ondelete('cascade')->nullble();
            $table->enum('niveau_alerte',['info','avertissement','urgence'])->default('info');
            $table->text('message_alerte');
            $table->datetime('date_alerte');
            $table->datetime('date_fin')->nullable();
            $table->enum('statut_alerte',['active','terminee'])->default('active');
            $table->foreignId('ville_id')->constrained('villes')->onDelete('cascade');
            $table->foreignId('secteur_id')->constrained('secteurs')->onDelete('cascade');
             $table->foreignId('unites_id')->constrained('unites')->onDelete('cascade');
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertes');
    }
};
