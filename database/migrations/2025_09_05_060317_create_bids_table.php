<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\BidStatus;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('load_id')->constrained('loads')->cascadeOnDelete();
            $table->foreignId('carrier_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->text('message')->nullable();
            $table->enum('status', array_map(fn($c) => $c->value, BidStatus::cases()))
                ->default(BidStatus::Pending->value);
            $table->timestamps();
            $table->unique(['load_id', 'carrier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};
