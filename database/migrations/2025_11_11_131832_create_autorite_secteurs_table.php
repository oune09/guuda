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
        Schema::create('autorite_secteurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('autorite_id')->constrained('autorite')->onDelete('cascade');
            $table->foreignId('secteur_id')->constrained('secteur')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['admin_id', 'secteur_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('autorite_secteurs');
    }
};
