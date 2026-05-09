<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('driver_id')->nullable();
            $table->string('recipient_name');
            $table->string('recipient_phone', 20);
            $table->text('pickup_address');
            $table->decimal('pickup_lat', 10, 7)->nullable();
            $table->decimal('pickup_lng', 10, 7)->nullable();
            $table->text('delivery_address');
            $table->decimal('delivery_lat', 10, 7)->nullable();
            $table->decimal('delivery_lng', 10, 7)->nullable();
            $table->enum('status', ['pending', 'assigned', 'in_transit', 'delivered', 'failed', 'cancelled'])->default('pending');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->text('notes')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('pickup_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->integer('estimated_duration')->nullable()->comment('minutes');
            $table->integer('actual_duration')->nullable()->comment('minutes');
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->text('delay_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'driver_id']);
            $table->index('created_at');
        });
    }

    public function down(): void { Schema::dropIfExists('delivery_orders'); }
};
