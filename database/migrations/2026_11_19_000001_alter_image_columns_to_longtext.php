<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->longText('proof_image')->nullable()->change();
        });

        Schema::table('fields', function (Blueprint $table) {
            $table->longText('image')->nullable()->change();
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->longText('image')->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('proof_image')->nullable()->change();
        });

        Schema::table('fields', function (Blueprint $table) {
            $table->string('image')->nullable()->change();
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->string('image')->change();
        });
    }
};
