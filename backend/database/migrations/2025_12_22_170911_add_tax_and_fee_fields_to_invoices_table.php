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
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('file_id')->nullable()->constrained('files')->onDelete('cascade');
            $table->decimal('total_tax_amount', 15, 2)->nullable();
            $table->decimal('auditor_fee', 15, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['file_id']);
            $table->dropColumn(['total_tax_amount', 'auditor_fee']);
        });
    }
};
