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
        // Cek apakah hari ini adalah hari Minggu (0)
        if (now()->dayOfWeek === 0) {
            $this->info('Tidak ada absensi yang dibuat karena hari ini adalah Minggu.');
            return;
        }

        $usersEmail = [
            'mat@aws.com',
            'gomedsulas@gmail.com'
        ];
        $users = User::query()
            ->whereIn('email', $usersEmail)
            ->get();

        foreach ($users as $key => $value) {
            Attendance::create([
                'user_id' => $value->id,
                'shift_id' => 2,
                'barcode_id' => 1,
                'time_in' => '07:57:13',
                'time_out' => '14:07:46',
                'date' => now()->toDateString(),
                'latitude' => 1.0353889776308,
                'longitude' => 480.82285695705,
                'status' => 'present',
            ]);
            $this->info('Absensi created for user ' . $value->name);
        }
    }
}
