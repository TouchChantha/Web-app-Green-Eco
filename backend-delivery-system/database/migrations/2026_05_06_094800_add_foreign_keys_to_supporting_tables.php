<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');
        });

        Schema::table('delivery_routes', function (Blueprint $table) {
            $table->foreign('delivery_order_id')->references('id')->on('delivery_orders')->onDelete('cascade');
        });

        Schema::table('delivery_stops', function (Blueprint $table) {
            $table->foreign('delivery_order_id')->references('id')->on('delivery_orders')->onDelete('cascade');
        });

        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->foreign('delivery_order_id')->references('id')->on('delivery_orders')->onDelete('cascade');
        });

        Schema::table('driver_location_logs', function (Blueprint $table) {
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('delivery_order_id')->references('id')->on('delivery_orders')->onDelete('set null');
        });

        Schema::table('driver_performances', function (Blueprint $table) {
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('driver_performances', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
        });

        Schema::table('driver_location_logs', function (Blueprint $table) {
            $table->dropForeign(['delivery_order_id']);
            $table->dropForeign(['driver_id']);
        });

        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->dropForeign(['delivery_order_id']);
        });

        Schema::table('delivery_stops', function (Blueprint $table) {
            $table->dropForeign(['delivery_order_id']);
        });

        Schema::table('delivery_routes', function (Blueprint $table) {
            $table->dropForeign(['delivery_order_id']);
        });

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
        });
    }
};