<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_products', function (Blueprint $table) {
            $table->id();
            $table->string('service_code', 100);
            $table->string('service_name', 100);
            $table->string('service_icon');
            $table->decimal('service_tariff', 20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_products');
    }
};
