<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE bookings MODIFY COLUMN status VARCHAR(40) NOT NULL DEFAULT 'pending_payment'");
            }
        } catch (\Throwable $e) {
        }

        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('expired_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'started_at')) {
                $table->dropColumn('started_at');
            }
        });
    }
};
