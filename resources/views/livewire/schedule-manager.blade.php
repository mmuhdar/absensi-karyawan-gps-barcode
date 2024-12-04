<div class="p-6 bg-gray-100 dark:bg-gray-800 rounded-lg shadow-md w-full">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Pengaturan Jadwal Shift</h1>

    <!-- Pilih Bulan dan Tahun -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div>
            <label for="month" class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Bulan</label>
            <select wire:model="month" id="month"
                class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label for="year" class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Tahun</label>
            <select wire:model="year" id="year"
                class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                @for ($y = now()->year - 5; $y <= now()->year + 5; $y++)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    <!-- Tabel Kalender -->
    <form wire:submit.prevent="save" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-300 dark:border-gray-600">
                <thead>
                    <tr class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                        @foreach (['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day)
                            <th class="px-2 py-1 text-center border">{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $daysInMonth = \Carbon\Carbon::create($year, $month)->daysInMonth;
                        $startDayOfWeek = \Carbon\Carbon::create($year, $month, 1)->dayOfWeek;
                        $weeks = ceil(($daysInMonth + $startDayOfWeek) / 7);
                        $day = 1 - $startDayOfWeek;
                    @endphp

                    @for ($week = 1; $week <= $weeks; $week++)
                        <tr>
                            @for ($d = 0; $d < 7; $d++)
                                @php
                                    $formattedDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                @endphp

                                @if ($day > 0 && $day <= $daysInMonth)
                                    <td class="border px-3 py-2 text-center">
                                        <div class="flex flex-col items-center">
                                            <span
                                                class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $day }}</span>
                                            <select wire:model="schedule.{{ $formattedDate }}"
                                                @disabled($formattedDate < now()->format('Y-m-d'))
                                                class="block disabled:opacity-60 disabled:cursor-not-allowed w-full mt-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2.5 text-xs text-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                                                :class="{
                                                    'bg-green-100 dark:bg-green-800': $el.value !== '',
                                                    'bg-gray-100 dark:bg-gray-800': $el.value === '',
                                                }">
                                                <option value="" class="text-gray-400 dark:text-gray-500">-- Pilih
                                                    Shift --</option>
                                                @foreach ($shifts as $shift)
                                                    <option value="{{ $shift->id }}"
                                                        @if (isset($schedule[$formattedDate]) && $schedule[$formattedDate] == $shift->id) selected @endif>
                                                        {{ $shift->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                @else
                                    <td class="border px-2 py-1 bg-gray-100 dark:bg-gray-700"></td>
                                @endif

                                @php $day++; @endphp
                            @endfor
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <button type="submit"
            class="mt-4 w-full rounded-lg bg-blue-500 dark:bg-blue-700 py-2 px-4 text-sm font-medium text-white hover:bg-blue-600 dark:hover:bg-blue-800 focus:outline-none focus:ring focus:ring-blue-300">
            Simpan Jadwal
        </button>
    </form>

    @if (session()->has('message'))
        <div class="mt-4 rounded-lg bg-green-100 dark:bg-green-800 p-4 text-green-700 dark:text-green-200">
            {{ session('message') }}
        </div>
    @endif
</div>
