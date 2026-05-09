<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * JWT token blacklist table — used by php-open-source-saver/jwt-auth
     * when CACHE_STORE is set to 'database'.
     * If you use Redis or file cache this table is not strictly needed,
     * but it is safe to create it regardless.
     */
    public function up(): void
    {
        // The jwt-auth package uses the Laravel cache driver for blacklisting.
        // No extra table is needed beyond what the cache driver requires.
        // This migration is intentionally a no-op placeholder so the
        // migration history stays clean.
    }

    public function down(): void
    {
        //
    }
};
