<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update files with status 'pending-info' to 'received'
        DB::table('files')->where('status', 'pending-info')->update(['status' => 'received']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to 'pending-info' if needed
        DB::table('files')->where('status', 'received')->update(['status' => 'pending-info']);
    }
};
