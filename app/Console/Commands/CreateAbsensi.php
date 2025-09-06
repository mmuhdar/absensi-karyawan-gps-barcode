<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Holiday;
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
    protected $description = 'Generate absensi harian untuk user tertentu, dengan memperhatikan weekend & libur nasional';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $usersEmail = [
            'mmuhdar08@gmail.com',
            'gomedsulas@gmail.com',
            'indraardiansyah2100@gmail.com',
            'aripeksi123@gmail.com',
            'ileilham47@gmail.com',
            'andimohsidik01@gmail.com',
            'mat@aws.com',
        ];

        $users = User::query()
            ->whereIn('email', $usersEmail)
            ->get();

        function randomTime($hour, $minStart, $minEnd)
        {
            $minute = rand($minStart, $minEnd);
            $second = rand(0, 59);
            return sprintf('%02d:%02d:%02d', $hour, $minute, $second);
        }

        $today = now()->toDateString();
        $isWeekend = now()->dayOfWeek === 0 || now()->dayOfWeek === 6;

        // 🔹 cek libur nasional dari database
        $holiday = Holiday::where('date', $today)->first();
        $isHoliday = $holiday !== null;

        foreach ($users as $user) {
            $timeIn = randomTime(8, 0, 37);
            $timeOut = randomTime(16, 0, 17);

            // Jika bukan weekend & bukan libur nasional, atau user tertentu
            if ((!$isWeekend && !$isHoliday) || $user->email === 'aripeksi123@gmail.com') {
                Attendance::create([
                    'user_id' => $user->id,
                    'shift_id' => 2,
                    'barcode_id' => 1,
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'date' => $today,
                    'latitude' => 1.0353889776308,
                    'longitude' => 480.82285695705,
                    'status' => 'present',
                    'note' => null,
                ]);
            } else {
                Attendance::create([
                    'user_id' => $user->id,
                    'shift_id' => null,
                    'barcode_id' => null,
                    'time_in' => null,
                    'time_out' => null,
                    'date' => $today,
                    'latitude' => 1.0353889776308,
                    'longitude' => 480.82285695705,
                    'status' => 'holiday',
                    'note' => $holiday->name ?? 'Libur',
                ]);
            }

            $this->info('Absensi created for user ' . $user->name);
        }
    }
}
