<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Cofres: guardam dinheiro. O saldo fica na linha, e cada depósito ou saque vira uma movimentação.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaults', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->integer('balance_cents')->default(0);
            $table->timestamps();
        });

        Schema::create('vault_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vault_id')->constrained()->cascadeOnDelete();
            $table->string('type', 10);           // deposit ou withdraw
            $table->unsignedInteger('amount_cents');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vault_movements');
        Schema::dropIfExists('vaults');
    }
};
