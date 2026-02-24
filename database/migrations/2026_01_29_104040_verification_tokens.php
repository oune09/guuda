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
        Schema::create('verification_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id') ->constrained('utilisateurs')->onDelete('cascade');
            $table->string('token'); 
            $table->enum('canal', ['email', 'phone']);
            $table->enum('type', ['activation', 'otp', 'reset_password']);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
      });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
