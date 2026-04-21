<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('profil_competence', function (Blueprint $table) {
            $table->foreignId('profil_id')->constrained('profils')->cascadeOnDelete();
            $table->foreignId('competence_id')->constrained('competences')->cascadeOnDelete();
            $table->enum('niveau', ['débutant', 'intermédiaire', 'expert']);
            $table->primary(['profil_id', 'competence_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_competence');
    }
};