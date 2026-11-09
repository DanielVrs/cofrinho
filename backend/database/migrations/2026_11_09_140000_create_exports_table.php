<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Um pedido de exportação: a API cria a linha como pending, o worker muda para done ou failed
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('month', 7);              // 2026-11
            $table->string('status', 10)->default('pending');
            $table->string('path')->nullable();      // onde o CSV ficou no storage
            $table->text('error')->nullable();       // mensagem quando falha
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exports');
    }
};
