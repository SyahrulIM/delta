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
            $table->string('code')->unique();            // kode produk unik
            $table->string('name');                      // nama produk
            $table->text('description')->nullable();     // deskripsi

            $table->integer('qty')->default(0);          // stok
            $table->string('unit', 50);                  // satuan (pcs, box)

            $table->integer('min_stock')->nullable();    // batas minimum stok
            $table->integer('max_stock')->nullable();    // batas maksimum

            $table->decimal('price', 14, 2)->nullable(); // harga jual
            $table->decimal('cost', 14, 2)->nullable();  // HPP

            $table->boolean('is_active')->default(true); // status produk
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
