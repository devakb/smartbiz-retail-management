<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->boolean('is_walk_in')->default(false);
            $table->boolean('is_active')->default(true);
            $table->decimal('outstanding_balance', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_walk_in');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
