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
        Schema::create('preuves', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('incident_id')->constrained('incidents')->onDelete('cascade');
            $table->string('nom_preuve');
            $table->string('type_preuve');
            $table->string('lien_preuve');
            $table->text('description_preuve')->nullable();
            $table->enum('statut_preuve',['valide','invalide','en_entente'])->default('en_entente');
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preuves');
    }
};
