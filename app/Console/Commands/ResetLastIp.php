<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetLastIp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-last-ip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset last_ip column in users daily';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('users')->update(['last_ip' => null]);
        $this->info('Berhasil Reset Semua Ip');
    }
}
