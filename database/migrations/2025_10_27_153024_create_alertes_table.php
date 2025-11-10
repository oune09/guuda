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
            $table->boolean('lu')->default('false');
            $table->datetime('date_alerte');
            $table->datetime('date_fin')->nullable();
            $table->enum('statut_alerte',['active','terminee'])->default('active');
            $table->string('ville');
            $table->string('secteur');
            $table->string('quartier');
            $table->string('longitude');
            $table->string('latitude');
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
