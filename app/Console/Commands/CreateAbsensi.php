<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateAbsensi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:absensi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Ambil user tertentu (misalnya berdasarkan ID)
        $user = User::find("01jcsw880ez6dyjqncy2x5gwnm");
        // $user = User::find("01jcyhwpvwabycz3qevkwxh14p");

        // Buat absensi untuk user tersebut
        Attendance::create([
            'user_id' => $user->id,
            'shift_id' => 2,
            'barcode_id' => 1,  // Menambahkan tanggal hari ini
            // 'time_in' => now()->format('H:i:s'),
            'time_in' => '07:57:13',
            'time_out' => '14:07:46',
            'date' => now()->toDateString(),
            'latitude' => 1.0353889776308,
            'longitude' => 480.82285695705,
            'status' => 'present',
        ]);

        $this->info('Absensi created for user ' . $user->name);
    }
}
