<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('delivery_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id');
            $table->unsignedTinyInteger('sequence');
            $table->text('address');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->enum('type', ['pickup', 'delivery', 'waypoint']);
            $table->enum('status', ['pending', 'reached', 'completed', 'skipped'])->default('pending');
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('departed_at')->nullable();
            $table->integer('stop_duration')->nullable()->comment('minutes');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id');
            $table->foreignId('changed_by')->constrained('users');
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('notes')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamps();
        });

        Schema::create('driver_location_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id');
            $table->foreignId('delivery_order_id')->nullable();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->decimal('speed_kmh', 6, 2)->default(0);
            $table->decimal('heading', 6, 2)->nullable();
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->boolean('is_stopped')->default(false);
            $table->timestamp('logged_at');
            $table->timestamps();

            $table->index(['driver_id', 'logged_at']);
            $table->index(['delivery_order_id', 'logged_at']);
        });

        Schema::create('driver_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id');
            $table->date('period_date');
            $table->unsignedInteger('total_orders')->default(0);
            $table->unsignedInteger('completed_orders')->default(0);
            $table->unsignedInteger('failed_orders')->default(0);
            $table->unsignedInteger('on_time_deliveries')->default(0);
            $table->unsignedInteger('late_deliveries')->default(0);
            $table->decimal('avg_delivery_time', 8, 2)->default(0)->comment('minutes');
            $table->decimal('total_distance_km', 10, 2)->default(0);
            $table->unsignedInteger('total_idle_time')->default(0)->comment('minutes');
            $table->decimal('on_time_rate', 5, 2)->default(0);
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->decimal('performance_score', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['driver_id', 'period_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_performances');
        Schema::dropIfExists('driver_location_logs');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('delivery_stops');
    }
};
