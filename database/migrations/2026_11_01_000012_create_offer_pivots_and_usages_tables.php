<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['offer_id', 'product_id']);
        });

        Schema::create('offer_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['offer_id', 'category_id']);
        });

        Schema::create('offer_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['offer_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_usages');
        Schema::dropIfExists('offer_categories');
        Schema::dropIfExists('offer_products');
    }
};
