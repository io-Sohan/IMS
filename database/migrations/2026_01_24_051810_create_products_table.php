<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->string('product_name');
            $table->string('sku')->unique();
            $table->string('unit'); // pcs, kg, box, etc.
            $table->string('image_path')->nullable();
            $table->integer('low_stock_threshold')->default(0);
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->integer('stock_qty')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
