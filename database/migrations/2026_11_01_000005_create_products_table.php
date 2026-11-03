<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('upc')->unique();
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tax_class_id')->nullable()->constrained('tax_classes')->nullOnDelete();
            $table->decimal('mrp', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('min_stock_level', 12, 3)->default(0);
            $table->string('unit')->default('pcs');
            $table->string('image')->nullable();
            $table->decimal('stock_quantity', 12, 3)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('category_id');
            $table->index('brand_id');
            $table->index('tax_class_id');
            $table->index('is_active');
            $table->index('stock_quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
