<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fields', function (Blueprint $table) {
            if (! Schema::hasColumn('fields', 'open_hour')) {
                $table->unsignedTinyInteger('open_hour')->default(8)->after('price_per_hour');
            }
            if (! Schema::hasColumn('fields', 'close_hour')) {
                $table->unsignedTinyInteger('close_hour')->default(23)->after('open_hour');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fields', function (Blueprint $table) {
            if (Schema::hasColumn('fields', 'open_hour')) {
                $table->dropColumn('open_hour');
            }
            if (Schema::hasColumn('fields', 'close_hour')) {
                $table->dropColumn('close_hour');
            }
        });
    }
};
