<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('load_id')->constrained('loads')->cascadeOnDelete();
            $table->foreignId('bid_id')->constrained('bids')->cascadeOnDelete();
            $table->foreignId('carrier_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('booked_at');
            $table->timestamps();
            $table->unique('load_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
