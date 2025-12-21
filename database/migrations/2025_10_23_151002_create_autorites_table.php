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
        Schema::create('autorites', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->foreignId('organisation_id')->constrained('organisations')->OnDelete('cascade');
            $table->string('matricule');
            $table->foreignId('unite_id')->constrained('unites')->onDelete('cascade');
            $table->boolean('statut')->default(true);
            $table->unique(['utilisateur_id','unite_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('autorites');
    }
};
