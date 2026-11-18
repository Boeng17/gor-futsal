<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('field_maintenance_blocks')) {
            Schema::create('field_maintenance_blocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('field_id')->constrained('fields')->cascadeOnDelete();
                $table->date('block_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->string('reason', 255);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('field_maintenance_blocks');
    }
};
