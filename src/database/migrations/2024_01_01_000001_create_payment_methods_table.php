<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_provider_id')
                ->constrained('payment_providers')
                ->cascadeOnDelete();

            $table->string('method_key')->index(); // Ex: 'pix', 'credit_card', 'boleto'
            $table->string('name');
            $table->string('image')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['payment_provider_id', 'method_key'], 'unique_method_per_provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
