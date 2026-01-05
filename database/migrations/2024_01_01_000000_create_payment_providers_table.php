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
        Schema::create('payment_providers', function (Blueprint $table) {
            $table->id();
            // Identificador técnico e legível
            $table->string('provider_key')->index(); // Ex: 'mercadopago', 'stripe', 'paypal'
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('image')->nullable();

            // Controle de ativação
            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->unique(['app_config_id', 'provider_key'], 'unique_provider_per_app');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_providers');
    }
};
