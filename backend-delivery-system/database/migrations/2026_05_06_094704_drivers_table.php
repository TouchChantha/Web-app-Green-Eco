<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('license_number')->unique();
            $table->enum('vehicle_type', ['motorcycle', 'car', 'van', 'truck']);
            $table->string('vehicle_plate', 20);
            $table->enum('status', ['available', 'on_delivery', 'offline'])->default('offline');
            $table->decimal('current_lat', 10, 7)->nullable();
            $table->decimal('current_lng', 10, 7)->nullable();
            $table->timestamp('last_location_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('drivers'); }
};
