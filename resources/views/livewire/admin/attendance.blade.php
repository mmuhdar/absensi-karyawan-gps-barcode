@php
    use Illuminate\Support\Carbon;
    $m = Carbon::parse($month);
    $showUserDetail = !$month || $week || $date; // is week or day filter
    $isPerDayFilter = isset($date);
@endphp
<div>
    <div class="flex justify-between w-full">
        <h3 class="col-span-2 mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Data Absensi
        </h3>
        @if (Auth::user()->isSuperAdmin)
            <div class="flex items-center gap-2">
                <x-button type="button" wire:click="triggerAttendanceModal">
                    {{ __('Buat Absensi Manual') }}
                </x-button>
            </div>
        @endif
    </div>

    @livewire('create-attendance-modal')

    <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        {{-- Filter Per Bulan --}}
        <div class="flex flex-col gap-2 lg:flex-row lg:items-center">
            <x-label for="month_filter" value="Per Bulan" />
            <x-input type="month" id="month_filter" name="month_filter" wire:model.live="month" class="w-full" />
        </div>

        {{-- Filter Per Minggu --}}
        <div class="flex flex-col gap-2 lg:flex-row lg:items-center">
            <x-label for="week_filter" value="Per Minggu" />
            <x-input type="week" id="week_filter" name="week_filter" wire:model.live="week" class="w-full" />
        </div>

        {{-- Filter Per Hari --}}
        <div class="flex flex-col gap-2 lg:flex-row lg:items-center sm:col-span-2 lg:col-span-1">
            <x-label for="day_filter" value="Per Hari" />
            <x-input type="date" id="day_filter" name="day_filter" wire:model.live="date" class="w-full" />
        </div>

        {{-- Divisi --}}
        <x-select id="division" wire:model.live="division" class="w-full">
            <option value="">{{ __('Select Division') }}</option>
            @foreach (App\Models\Division::all() as $_division)
                <option value="{{ $_division->id }}" {{ $_division->id == $division ? 'selected' : '' }}>
                    {{ $_division->name }}
                </option>
            @endforeach
        </x-select>

        {{-- Jabatan --}}
        <x-select id="jobTitle" wire:model.live="jobTitle" class="w-full">
            <option value="">{{ __('Select Job Title') }}</option>
            @foreach (App\Models\JobTitle::all() as $_jobTitle)
                <option value="{{ $_jobTitle->id }}" {{ $_jobTitle->id == $jobTitle ? 'selected' : '' }}>
                    {{ $_jobTitle->name }}
                </option>
            @endforeach
        </x-select>

        {{-- Ruangan --}}
        <x-select id="roomId" wire:model.live="roomId" class="w-full">
            <option value="">{{ __('Pilih Ruangan') }}</option>
            @foreach (App\Models\Room::all() as $_room)
                <option value="{{ $_room->id }}" {{ $_room->id == $roomId ? 'selected' : '' }}>
                    {{ $_room->name }}
                </option>
            @endforeach
        </x-select>

        {{-- Search Box --}}
        <div class="flex items-center gap-2 sm:col-span-2 lg:col-span-2">
            <x-input type="text" id="search" name="search" class="w-full" wire:model="search"
                placeholder="{{ __('Search') }}" />
            <x-button type="button" wire:click="$refresh" wire:loading.attr="disabled">
                {{ __('Search') }}
            </x-button>
            @if ($search)
                <x-secondary-button type="button" wire:click="$set('search', '')" wire:loading.attr="disabled">
                    {{ __('Reset') }}
                </x-secondary-button>
            @endif
        </div>

        {{-- Cetak Laporan --}}
        <div class="sm:col-span-2 lg:col-span-1">
            <x-secondary-button
                href="{{ route('admin.attendances.report', [
                    'month' => $month,
                    'week' => $week,
                    'date' => $date,
                    'division' => $division,
                    'jobTitle' => $jobTitle,
                ]) }}"
                class="flex w-full justify-center gap-2">
                Cetak Laporan
                <x-heroicon-o-printer class="h-5 w-5" />
            </x-secondary-button>
        </div>
    </div>

    <div class="overflow-x-scroll">
        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                        {{ $showUserDetail ? __('Name') : __('Name') . '/' . __('Date') }}
                    </th>
                    @if ($showUserDetail)
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                            {{ __('Ruangan') }}
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                            {{ __('Division') }}
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                            {{ __('Job Title') }}
                        </th>
                        @if ($isPerDayFilter)
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                                {{ __('Shift') }}
                            </th>
                        @endif
                    @endif
                    @foreach ($dates as $date)
                        @php
                            if (!$isPerDayFilter && $date->isSunday()) {
                                // Minggu merah
                                $textClass = 'text-red-500 dark:text-red-300';
                            } elseif (!$isPerDayFilter && $date->isFriday()) {
                                // Jumat hijau
                                $textClass = 'text-green-500 dark:text-green-300';
                            } else {
                                $textClass = 'text-gray-500 dark:text-gray-300';
                            }
                        @endphp
                        <th scope="col"
                            class="{{ $textClass }} text-nowrap border border-gray-300 px-1 py-3 text-center text-xs font-medium dark:border-gray-600">
                            @if ($isPerDayFilter)
                                Status
                            @else
                                {{ $date->format('d/m') }}
                            @endif
                        </th>
                    @endforeach
                    @if ($isPerDayFilter)
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                            {{ __('Time In') }}
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                            {{ __('Time Out') }}
                        </th>
                    @endif
                    @if (!$isPerDayFilter)
                        @foreach (['H', 'T', 'I', 'S', 'A', 'C', 'DL', 'LJ', 'L'] as $_st)
                            <th scope="col"
                                class="text-nowrap border border-gray-300 px-1 py-3 text-center text-xs font-medium text-gray-500 dark:border-gray-600 dark:text-gray-300">
                                {{ $_st }}
                            </th>
                        @endforeach
                    @endif
                    @if ($isPerDayFilter)
                        <th scope="col" class="relative">
                            <span class="sr-only">Actions</span>
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                @php
                    $class = 'cursor-pointer px-4 py-3 text-sm font-medium text-gray-900 dark:text-white';
                @endphp
                @foreach ($employees as $employee)
                    @php
                        $attendances = $employee->attendances;
                        $schedules = \App\Models\EmployeeSchedule::with('shift')
                            ->where('user_id', $employee->id)
                            ->get();
                        $schedule = $schedules->firstWhere(fn($v, $_) => $v['date'] === $date->format('Y-m-d'));
                    @endphp
                    <tr wire:key="{{ $employee->id }}" class="group">
                        {{-- Detail karyawan --}}
                        <td
                            class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                            {{ $employee->name }}
                        </td>
                        @if ($showUserDetail)
                            <td
                                class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                {{ $employee->room?->name ?? '-' }}
                            </td>
                            <td
                                class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                {{ $employee->division?->name ?? '-' }}
                            </td>
                            <td
                                class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                {{ $employee->jobTitle?->name ?? '-' }}
                            </td>
                            @if ($isPerDayFilter)
                                @php
                                    $attendance = $employee->attendances->isEmpty()
                                        ? null
                                        : $employee->attendances->first();
                                    $timeIn = $attendance ? $attendance['time_in'] : null;
                                    $timeOut = $attendance ? $attendance['time_out'] : null;
                                @endphp
                                {{-- <td
                                    class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                    {{ $attendance['shift'] ?? ($schedule?->shift?->name ?? '-') }}
                                </td> --}}
                                @if ($schedule)
                                    <td
                                        class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                        {{ $schedule->shift?->name ?? '-' }}
                                    </td>
                                @else
                                    <td
                                        class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                        {{ $attendance->shift?->name ?? 'Shift belum diatur' }}
                                    </td>
                                @endif
                            @endif
                        @endif

                        {{-- Absensi --}}
                        @php
                            $presentCount = 0;
                            $lateCount = 0;
                            $excusedCount = 0;
                            $sickCount = 0;
                            $absentCount = 0;
                            $holidayCount = 0;
                            $cutiCount = 0;
                            $dinasCount = 0;
                            $lepasJagaCount = 0;
                        @endphp
                        @foreach ($dates as $date)
                            @php
                                $attendance = $attendances->firstWhere(
                                    fn($v, $k) => $v['date'] === $date->format('Y-m-d'),
                                );
                                $statusLibur = false;
                                if ($schedule) {
                                    $statusLibur = $schedule->shift->name == 'Libur' ? true : false;
                                }
                                $status = ($attendance ?? [
                                    'status' => $statusLibur || !$date->isPast() ? '-' : 'absent',
                                ])['status'];
                                switch ($status) {
                                    case 'present':
                                        $shortStatus = 'H';
                                        $bgColor =
                                            'bg-green-200 dark:bg-green-800 hover:bg-green-300 dark:hover:bg-green-700 border border-green-300 dark:border-green-600';
                                        $presentCount++;
                                        break;
                                    case 'late':
                                        $shortStatus = 'T';
                                        $bgColor =
                                            'bg-amber-200 dark:bg-amber-800 hover:bg-amber-300 dark:hover:bg-amber-700 border border-amber-300 dark:border-amber-600';
                                        $lateCount++;
                                        break;
                                    case 'excused':
                                        $shortStatus = 'I';
                                        $bgColor =
                                            'bg-blue-200 dark:bg-blue-800 hover:bg-blue-300 dark:hover:bg-blue-700 border border-blue-300 dark:border-blue-600';
                                        $excusedCount++;
                                        break;
                                    case 'sick':
                                        $shortStatus = 'S';
                                        $bgColor =
                                            'hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600';
                                        $sickCount++;
                                        break;
                                    case 'absent':
                                        $shortStatus = 'A';
                                        $bgColor =
                                            'bg-red-200 dark:bg-red-800 hover:bg-red-300 dark:hover:bg-red-700 border border-red-300 dark:border-red-600';
                                        $absentCount++;
                                        break;
                                    case 'holiday':
                                        $shortStatus = 'L';
                                        $bgColor =
                                            'bg-cyan-200 dark:bg-cyan-800 hover:bg-cyan-300 dark:hover:bg-cyan-700 border border-cyan-300 dark:border-cyan-600';
                                        $holidayCount++;
                                        break;
                                    case 'lepas_jaga':
                                        $shortStatus = 'LJ';
                                        $bgColor =
                                            'bg-cyan-200 dark:bg-cyan-800 hover:bg-cyan-300 dark:hover:bg-cyan-700 border border-cyan-300 dark:border-cyan-600';
                                        $lepasJagaCount++;
                                        break;
                                    case 'dinas_luar':
                                        $shortStatus = 'DL';
                                        $bgColor =
                                            'bg-green-200 dark:bg-green-800 hover:bg-green-300 dark:hover:bg-green-700 border border-green-300 dark:border-green-600';
                                        $dinasCount++;
                                        break;
                                    case 'cuti':
                                        $shortStatus = 'C';
                                        $bgColor =
                                            'bg-orange-200 dark:bg-orange-800 hover:bg-orange-300 dark:hover:bg-orange-700 border border-orange-300 dark:border-orange-600';
                                        $cutiCount++;
                                        break;
                                    default:
                                        $shortStatus = '-';
                                        $bgColor =
                                            'hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600';
                                        break;
                                }

                            @endphp
                            {{-- @if (!$isPerDayFilter && $attendance && ($attendance['attachment'] || $attendance['note'] || $attendance['coordinates'])) --}}
                            @if (!$isPerDayFilter && $attendance)
                                <td
                                    class="{{ $bgColor }} cursor-pointer text-center text-sm font-medium text-gray-900 dark:text-white">
                                    <button class="w-full px-1 py-3" wire:click="show({{ $attendance['id'] }})"
                                        onclick="setLocation({{ $attendance['lat'] ?? 0 }}, {{ $attendance['lng'] ?? 0 }})">
                                        {{ $isPerDayFilter ? __($status) : $shortStatus }}
                                    </button>
                                </td>
                            @else
                                <td
                                    class="{{ $bgColor }} text-nowrap cursor-pointer px-1 py-3 text-center text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $isPerDayFilter ? __($status) : $shortStatus }}
                                </td>
                            @endif
                        @endforeach

                        {{-- Waktu masuk/keluar --}}
                        @if ($isPerDayFilter)
                            <td class="{{ $class }} group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                {{ $timeIn ?? '-' }}
                            </td>
                            <td class="{{ $class }} group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                {{ $timeOut ?? '-' }}
                            </td>
                        @endif

                        {{-- Total --}}
                        @if (!$isPerDayFilter)
                            @foreach ([$presentCount, $lateCount, $excusedCount, $sickCount, $absentCount, $cutiCount, $dinasCount, $lepasJagaCount, $holidayCount] as $statusCount)
                                <td
                                    class="cursor-pointer border border-gray-300 px-1 py-3 text-center text-sm font-medium text-gray-900 group-hover:bg-gray-100 dark:border-gray-600 dark:text-white dark:group-hover:bg-gray-700">
                                    {{ $statusCount }}
                                </td>
                            @endforeach
                        @endif

                        {{-- Action --}}
                        @if ($isPerDayFilter)
                            @php
                                $attendance = $employee->attendances->isEmpty()
                                    ? null
                                    : $employee->attendances->first();
                            @endphp
                            <td
                                class="cursor-pointer text-center text-sm font-medium text-gray-900 group-hover:bg-gray-100 dark:text-white dark:group-hover:bg-gray-700">
                                <div class="flex items-center justify-center gap-3">
                                    @if ($attendance && ($attendance['attachment'] || $attendance['note'] || $attendance['coordinates']))
                                        <x-button type="button" wire:click="show({{ $attendance['id'] }})"
                                            onclick="setLocation({{ $attendance['lat'] ?? 0 }}, {{ $attendance['lng'] ?? 0 }})">
                                            {{ __('Detail') }}
                                        </x-button>
                                    @else
                                        -
                                    @endif
                                </div>

                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($employees->isEmpty())
        <div class="my-2 text-center text-sm font-medium text-gray-900 dark:text-gray-100">
            Tidak ada data
        </div>
    @endif
    <div class="mt-3">
        {{ $employees->links() }}
    </div>

    <x-attendance-detail-modal :current-attendance="$currentAttendance" />
    @stack('attendance-detail-scripts')
</div>
