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
        Schema::table('files', function (Blueprint $table) {
            $table->string('financial_year')->nullable()->after('assessment_year');
            $table->date('payment_request_date')->nullable()->after('actual_completion_date');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null')->after('payment_request_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropColumn(['financial_year', 'payment_request_date', 'payment_id']);
        });
    }
};
