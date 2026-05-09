<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('delivery_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id');
            $table->longText('polyline')->nullable();
            $table->json('waypoints')->nullable();
            $table->decimal('total_distance_km', 8, 2)->nullable();
            $table->integer('estimated_duration')->nullable()->comment('minutes');
            $table->boolean('optimized')->default(false);
            $table->string('google_route_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('delivery_routes'); }
};
