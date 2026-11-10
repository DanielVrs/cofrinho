<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // chave que o cliente manda no cabeçalho Idempotency-Key; nula quando ele não manda
            $table->string('idempotency_key', 64)->nullable()->after('user_id');

            // índice único por usuário: o banco recusa o segundo insert com a mesma chave.
            // Nulos não contam no índice, então requisições sem chave continuam livres.
            $table->unique(['user_id', 'idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'idempotency_key']);
            $table->dropColumn('idempotency_key');
        });
    }
};
