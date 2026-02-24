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
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unite_id')->constrained('unites')->onDelete('cascade');
            $table->foreignId('autorite_id')->constrained('autorites')->onDelete('cascade');
            $table->boolean('statut')->default(true);
            $table->timestamps();

            $table->unique(['autorite_id','statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affectations');
    }
};
