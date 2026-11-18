<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Field extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price_per_hour',
        'open_hour',
        'close_hour',
        'status',
        'image',
    ];

    protected $attributes = [
        'open_hour'  => 8,
        'close_hour' => 23,
        'status'     => 'active',
    ];

    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function maintenanceBlocks(): HasMany
    {
        return $this->hasMany(FieldMaintenanceBlock::class);
    }

    public function getOpenHourValueAttribute(): int
    {
        return (int) ($this->attributes['open_hour'] ?? 8);
    }

    public function getCloseHourValueAttribute(): int
    {
        return (int) ($this->attributes['close_hour'] ?? 23);
    }

    public function getOperatingHoursLabelAttribute(): string
    {
        $open = str_pad((string) $this->open_hour_value, 2, '0', STR_PAD_LEFT).':00';
        $close = str_pad((string) $this->close_hour_value, 2, '0', STR_PAD_LEFT).':00';
        $duration = max(1, $this->close_hour_value - $this->open_hour_value);

        return "{$open} - {$close} WIB ({$duration} Jam)";
    }
}
