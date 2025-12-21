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
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->enum('niveau_alerte',['info','avertissement','urgence'])->default('info');
            $table->string('titre_alerte');
            $table->text('message_alerte');
            $table->datetime('date_alerte');
            $table->datetime('date_fin')->nullable();
            $table->enum('statut_alerte',['active','terminee'])->default('active');
            $table->foreignId('unite_id')->constrained('unites')->onDelete('cascade');
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->decimal('rayon_km',5,2)->default('5.00');
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
