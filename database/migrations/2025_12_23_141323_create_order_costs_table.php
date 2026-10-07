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
        Schema::create('order_costs', function (Blueprint $table) {
            $table->id();
            // Morph
            // $table->morphs('source'); // source_type, source_id
            $table->foreignId('sales_order_item_id')->constrained()->onDelete('cascade');

            // Cost components
            $table->decimal('purchase_cost', 15, 2)->default(0);
            $table->decimal('contractor_percent', 15, 2)->default(0);
            $table->decimal('expedition_percent', 15, 2)->default(0);
            $table->decimal('test_percent', 15, 2)->default(0);
            $table->decimal('marketing_percent', 15, 2)->default(0);
            $table->decimal('trip_percent', 15, 2)->default(0);
            $table->decimal('salary_percent', 15, 2)->default(0);
            $table->decimal('pph_percent', 15, 2)->default(0);
            $table->decimal('scf_percent', 15, 2)->default(0);
            $table->decimal('bunga_bank_percent', 15, 2)->default(0);
            $table->decimal('retensi_percent', 15, 2)->default(0);

            // Result
            $table->decimal('total_percent', 15, 2)->default(0);
            $table->decimal('harga_netto', 15, 2)->default(0);
            $table->decimal('total_netto', 15, 2)->default(0);
            $table->decimal('margin_value', 15, 2)->default(0);
            $table->decimal('margin_percent', 5, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_costs');
    }
};
