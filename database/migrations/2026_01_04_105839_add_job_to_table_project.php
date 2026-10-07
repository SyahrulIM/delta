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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('job', 100)->nullable();
            $table->string('contract_no', 100)->nullable();
            $table->string('customer_name', 100)->nullable();
            $table->string('phone', 100)->nullable();
            $table->string('pic', 100)->nullable();
            $table->string('npwp', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('job');
            $table->dropColumn('contract_no');
            $table->dropColumn('customer_name');
            $table->dropColumn('phone');
            $table->dropColumn('pic');
            $table->dropColumn('npwp');
        });
    }
};
