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
        $usersEmail = [
            'mmuhdar08@gmail.com',
            'gomedsulas@gmail.com',
            'ileilham47@gmail.com',
            'andimohsidik01@gmail.com',
            'indraardiansyah2100@gmail.com',
            'deswitawita100@gmail.com',
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

        foreach ($users as $key => $value) {
            $timeIn = randomTime(8, 0, 37);
            $timeOut = randomTime(14, 0, 28);

            if (now()->dayOfWeek === 0) {
                Attendance::create([
                    'user_id' => $value->id,
                    'shift_id' => null,
                    'barcode_id' => null,
                    'time_in' => null,
                    'time_out' => null,
                    'date' => now()->toDateString(),
                    'latitude' => 1.0353889776308,
                    'longitude' => 480.82285695705,
                    'status' => 'holiday',
                ]);
            } else {
                Attendance::create([
                    'user_id' => $value->id,
                    'shift_id' => 2,
                    'barcode_id' => 1,
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'date' => now()->toDateString(),
                    'latitude' => 1.0353889776308,
                    'longitude' => 480.82285695705,
                    'status' => 'present',
                ]);
            }

            $this->info('Absensi created for user ' . $value->name);
        }
    }
}
