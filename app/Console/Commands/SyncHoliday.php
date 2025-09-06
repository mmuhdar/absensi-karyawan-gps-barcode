<?php

namespace App\Console\Commands;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncHoliday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:holidays';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync libur nasional dari API libur.deno.dev ke database';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        try {
            $this->info("Mengambil data libur nasional untuk tahun ...");

            $response = Http::timeout(10)->get("https://libur.deno.dev/api");

            if ($response->failed()) {
                $this->error("Gagal ambil data dari API. HTTP Status: " . $response->status());
                return;
            }

            $holidays = $response->json();

            if (!is_array($holidays)) {
                $this->error("Format response API tidak valid.");
                return;
            }

            foreach ($holidays as $holiday) {
                $date = $holiday['date'] ?? null;
                $name = $holiday['name'] ?? null;

                if ($date && $name) {
                    Holiday::updateOrCreate(
                        ['date' => Carbon::parse($date)->toDateString()],
                        ['name' => $name]
                    );
                }
            }

            $this->info("Sync selesai. Total: " . count($holidays) . " hari libur disimpan.");
        } catch (\Exception $e) {
            $this->error("Terjadi error: " . $e->getMessage());
        }
    }
}
