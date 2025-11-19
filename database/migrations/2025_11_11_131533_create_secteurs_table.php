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
        Schema::create('secteurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ville_id')->constrained('ville')->onDelete('cascade');
            $table->foreignId('superAdmin_id')->constrained('admin')->onDelete('cascade');
            $table->string('nom_secteur');
            $table->unique(['ville_id', 'nom_secteur']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secteurs');
    }
};
