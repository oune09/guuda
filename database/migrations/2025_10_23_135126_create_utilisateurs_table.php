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
         Schema::create('utilisateurs', function (Blueprint $table) {
        //informations de base
        $table->id();
        $table->string('nom_utilisateur');
        $table->string('prenom_utilisateur');
        $table->string('email_utilisateur')->nullable()->unique();
        $table->string('mot_de_passe_utilisateur')->nullable();
        $table->string('telephone_utilisateur')->nullable()->unique();
        $table->string('photo')->nullable();
        $table->boolean('is_active')->default(false);
        $table->timestamp('verified_at')->nullable();
        $table->enum('verification_channel', ['email', 'phone'])->nullable();
        $table->decimal('latitude', 10, 7)->nullable();
        $table->decimal('longitude', 10, 7)->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->timestamp('phone_verified_at')->nullable();
        $table->rememberToken();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
