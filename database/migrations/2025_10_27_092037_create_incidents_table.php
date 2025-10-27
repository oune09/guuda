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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->string('type_incident');
            $table->text('description_incident');
            $table->datetime('date_incident');
            $table->enum('priorite',['faible','moyenne','elevee'])->default('moyenne');
            $table->enum('statut_incident',['ouvert','en_cours','resolu','ferme'])->default('ouvert');
            $table->string('quartier');
            $table->string('secteur');
            $table->string('ville');
            $table->decimal('longitude', 10 , 8);
            $table->decimal('latitude',10,8);

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
