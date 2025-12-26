<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('istilah', function (Blueprint $table) {
            $table->id();
            $table->string('istilah');
            $table->string('kategori');
            $table->text('definisi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('istilah');
    }
};
