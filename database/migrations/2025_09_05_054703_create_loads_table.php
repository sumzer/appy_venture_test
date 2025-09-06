<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\LoadStatus;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipper_id')->constrained('users')->cascadeOnDelete();
            $table->string('origin_country', 3)->nullable();
            $table->string('origin_city')->nullable();
            $table->string('destination_country', 3)->nullable();
            $table->string('destination_city')->nullable();
            $table->date('pickup_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->unsignedInteger('weight_kg')->nullable();
            $table->unsignedInteger('price_expectation')->nullable();
            $table->enum('status', array_map(fn($c) => $c->value, LoadStatus::cases()))
                ->default(LoadStatus::Draft->value)
                ->index();
            $table->unsignedBigInteger('version')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loads');
    }
};
