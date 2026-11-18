<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Field;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name'     => 'Admin GOR',
            'email'    => 'admin@gor.test',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Kasir Operasional',
            'email'    => 'kasir@gor.test',
            'password' => Hash::make('password'),
            'role'     => 'kasir',
        ]);

        Field::create([
            'name'           => 'Lapangan 1 (Vinyl Interlock)',
            'description'    => 'Lantai vinyl standar kompetisi dengan pencahayaan LED penuh.',
            'price_per_hour' => 120000,
            'status'         => 'active',
            'image'          => null,
        ]);

        Field::create([
            'name'           => 'Lapangan 2 (Rumput Sintetis)',
            'description'    => 'Rumput sintetis generasi terbaru dengan area tunggu luas.',
            'price_per_hour' => 100000,
            'status'         => 'active',
            'image'          => null,
        ]);

        Field::create([
            'name'           => 'Lapangan 3 (VIP Indoor)',
            'description'    => 'Lapangan indoor khusus pertandingan dan latihan privat.',
            'price_per_hour' => 150000,
            'status'         => 'maintenance',
            'image'          => null,
        ]);

        Banner::create([
            'created_by' => $admin->id,
            'title'      => 'Turnamen Futsal Akhir Pekan',
            'image'      => 'banners/default-event.jpg',
            'content'    => 'Pendaftaran tim dibuka setiap minggu. Hubungi kasir GOR untuk informasi jadwal dan kuota peserta.',
            'is_active'  => true,
        ]);
    }
}
