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
        Schema::create('sales_order_subs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_item_id')->constrained()->onDelete('cascade');
            $table->string('field');
            $table->date('sub_tanggal')->nullable();
            $table->decimal('sub_pembayaran', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_subs');
    }
};
