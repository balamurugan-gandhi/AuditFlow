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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('alternate_phone')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('pan_number')->unique()->nullable();
            $table->string('gst_number')->nullable();
            $table->string('tin_number')->nullable();
            $table->string('business_type')->nullable(); // Prop, Partnership, Pvt Ltd
            $table->string('filing_cycle')->default('yearly'); // yearly, quarterly, monthly
            $table->text('address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
