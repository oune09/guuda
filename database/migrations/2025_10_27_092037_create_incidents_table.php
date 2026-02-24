<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->foreignId('organisation_id')->constrained('organisations')->onDelete('cascade');
            $table->foreignId('unite_id')->constrained('unites')->onDelete('cascade');
            $table->foreignId('affectation_id')->nullable()->constrained('affectations');
            $table->datetime('date_incident');
            $table->dateTime('date_charge')->nullable();
            $table->dateTime('date_resolution')->nullable();
            $table->enum('statut_incident',['ouvert','en_cours','resolu','annulee'])->default('ouvert');
            $table->decimal('longitude', 13, 10); 
            $table->decimal('latitude', 12, 10);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
