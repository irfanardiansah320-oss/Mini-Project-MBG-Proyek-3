<?php

namespace Database\Seeders;

use App\Models\Distribution;
use App\Models\Report;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $rina = User::create([
            'name' => 'Bu Rina',
            'email' => 'pelapor@example.com',
            'password' => Hash::make('password'),
            'role' => 'pelapor',
        ]);

        $tini = User::create([
            'name' => 'Bu Tini',
            'email' => 'pelapor2@example.com',
            'password' => Hash::make('password'),
            'role' => 'pelapor',
        ]);

        User::create([
            'name' => 'Pak Dedi',
            'email' => 'pemantau@example.com',
            'password' => Hash::make('password'),
            'role' => 'pemantau',
        ]);

        User::create([
            'name' => 'Bu Sari',
            'email' => 'koordinator@example.com',
            'password' => Hash::make('password'),
            'role' => 'koordinator',
        ]);

        // Sekolah
        $harapan = School::create(['name' => 'SMP Harapan', 'address' => 'Jl Sarijadi']);
        $melati = School::create(['name' => 'SD Melati', 'address' => 'Jl Ciwaruga']);
        $taruna = School::create(['name' => 'SMA Taruna', 'address' => 'Jl Margahayu']);

        // Distribusi
        $d1 = Distribution::create(['school_id' => $harapan->id, 'distribution_date' => '2026-10-26', 'menu' => 'Nasi, ayam, sayur']);
        Distribution::create(['school_id' => $harapan->id, 'distribution_date' => '2026-10-29', 'menu' => 'Nasi, ikan, tahu']);
        $d3 = Distribution::create(['school_id' => $melati->id, 'distribution_date' => '2026-10-27', 'menu' => 'Nasi, telur, sayur']);
        Distribution::create(['school_id' => $taruna->id, 'distribution_date' => '2026-10-28', 'menu' => 'Nasi, daging, buah']);

        // Laporan dengan status berbeda
        Report::create([
            'user_id' => $rina->id, 'distribution_id' => $d1->id,
            'incident_date' => '2026-10-26',
            'complaint' => 'Anak mual dan sakit perut beberapa jam setelah makan.',
            'status' => 'dikirim',
        ]);
        Report::create([
            'user_id' => $tini->id, 'distribution_id' => $d1->id,
            'incident_date' => '2026-10-26',
            'complaint' => 'Anak mengeluh pusing dan mual sore hari.',
            'status' => 'ditinjau',
        ]);
        Report::create([
            'user_id' => $rina->id, 'distribution_id' => $d3->id,
            'incident_date' => '2026-10-27',
            'complaint' => 'Anak diare setelah makan di sekolah.',
            'status' => 'perlu_diteruskan',
        ]);
        Report::create([
            'user_id' => $tini->id, 'distribution_id' => $d3->id,
            'incident_date' => '2026-10-27',
            'complaint' => 'Anak mual setelah makan.',
            'status' => 'diteruskan',
        ]);
    }
}