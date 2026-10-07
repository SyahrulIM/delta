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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->string('ref_type');   // GR, DO, ADJUSTMENT, TRANSFER
            $table->bigInteger('ref_id'); // ID from related table
            $table->enum('movement_type', ['IN', 'OUT']);
            $table->integer('qty');
            $table->integer('qty_before');
            $table->integer('qty_after');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
