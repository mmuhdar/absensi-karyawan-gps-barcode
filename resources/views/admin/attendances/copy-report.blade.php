@php
    use Illuminate\Support\Carbon;
    $selectedDate = Carbon::parse($date ?? ($week ?? $month))->settings(['formatFunction' => 'translatedFormat']);
    $showUserDetail = !$month || $week || $date; // is week or day filter
    $isPerDayFilter = isset($date);
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Absensi | {{ $date ?? ($week ?? $month) }}</title>
    <style>
        @page {
            size: A4 landscape;
            /* Mengatur ukuran halaman ke A4 dan orientasi landscape */
            margin: 10mm;
            /* Mengatur margin agar cukup ruang */
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            /* Mengurangi ukuran font untuk menyesuaikan layout */
        }

        h1 {
            font-size: 18px;
            margin-bottom: 20px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #aaa;
            padding: 5px 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        td {
            vertical-align: middle;
            font-size: 10px;
        }

        /* Mengganti Tailwind dengan CSS standar */
        .status-h {
            background-color: #c6f6d5;
            /* Hijau muda untuk "present" */
            color: #2f855a;
            /* Warna teks hijau */
        }

        .status-t {
            background-color: #fefcbf;
            /* Kuning untuk "late" */
            color: #b7791f;
            /* Warna teks kuning tua */
        }

        .status-i {
            background-color: #bee3f8;
            /* Biru muda untuk "excused" */
            color: #2b6cb0;
            /* Warna teks biru */
        }

        .status-s {
            background-color: #edf2f7;
            /* Abu-abu terang untuk "sick" */
            color: #4a5568;
            /* Warna teks abu-abu */
        }

        .status-a {
            background-color: #fed7d7;
            /* Merah muda untuk "absent" */
            color: #c53030;
            /* Warna teks merah */
        }

        .status-l {
            background-color: #b2f5ea;
            /* Cyan untuk "holiday" */
            color: #2c7a7b;
            /* Warna teks cyan tua */
        }

        .status-c {
            background-color: #fed7aa;
            /* Oranye muda untuk "cuti" */
            color: #9c4221;
            /* Warna teks oranye */
        }

        .status-dl {
            background-color: #c6f6d5;
            /* Hijau muda untuk "dinas luar" */
            color: #2f855a;
            /* Warna teks hijau */
        }

        .status-lj {
            background-color: #b2f5ea;
            /* Cyan untuk "lepas jaga" */
            color: #2c7a7b;
            /* Warna teks cyan tua */
        }

        th,
        td {
            padding: 0px;
            font-size: 10px;
        }

        .right-align {
            text-align: right;
            padding-right: 10px;
        }

        .center-align {
            text-align: center;
        }

        .table-header {
            font-size: 11px;
        }

        /* Adjust column widths if necessary */
        td,
        th {
            width: 50px;
        }
    </style>
</head>

<body>
    <h1>Data Absensi</h1>

    <div style="display: table; width: 100%; margin-bottom: 20px">
        <div style="display: table-cell; width: 50%;">
            <table>
                @if ($division)
                    <tr>
                        <td>Divisi</td>
                        <td>:</td>
                        <td>{{ $division ? App\Models\Division::find($division)->name : '-' }}</td>
                    </tr>
                @endif
                @if ($jobTitle)
                    <tr>
                        <td>Jabatan</td>
                        <td>:</td>
                        <td>{{ $jobTitle ? App\Models\JobTitle::find($jobTitle)->name : '-' }}</td>
                    </tr>
                @endif
            </table>
        </div>
        <div style="display: table-cell; text-align: right; width: 50%;" class="right-align">
            @if ($month)
                Bulan: {{ $selectedDate->format('F Y') }}
            @elseif ($week)
                Tanggal: {{ $start->format('l, d/m/Y') }} - {{ $end->format('l, d/m/Y') }}
            @elseif ($date)
                Tanggal: {{ $selectedDate->format('d/m/Y') }}
            @endif
        </div>
    </div>

    <table id="table">
        <thead>
            <tr>
                <th scope="col" class="center-align">No.</th>
                <th scope="col" class="center-align">
                    {{ $showUserDetail ? __('Name') : __('Name') . '/' . __('Date') }}</th>
                @if ($showUserDetail)
                    <th scope="col" class="center-align">{{ __('NIP') }}</th>
                    <th scope="col" class="center-align">{{ __('Division') }}</th>
                    <th scope="col" class="center-align">{{ __('Job Title') }}</th>
                    @if ($isPerDayFilter)
                        <th scope="col" class="center-align">{{ __('Shift') }}</th>
                    @endif
                @endif
                @foreach ($dates as $date)
                    <th scope="col" class="center-align">{{ $isPerDayFilter ? 'Status' : $date->format('d/m') }}
                    </th>
                @endforeach
                @if (!$isPerDayFilter)
                    @foreach (['H', 'T', 'I', 'S', 'A', 'C', 'DL', 'LJ', 'L'] as $_st)
                        <th scope="col" class="center-align">{{ $_st }}</th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
                @php
                    $attendances = $employee->attendances;
                    $attendance = $employee->attendances->isEmpty() ? null : $employee->attendances->first();
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $employee->name }}</td>
                    @if ($showUserDetail)
                        <td>{{ $employee->nip }}</td>
                        <td>{{ $employee->division?->name ?? '-' }}</td>
                        <td>{{ $employee->jobTitle?->name ?? '-' }}</td>
                        @if ($isPerDayFilter)
                            <td>{{ $attendance['shift'] ?? '-' }}</td>
                        @endif
                    @endif
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
                            $isWeekend = $date->isWeekend();
                            $status = ($attendances->firstWhere(
                                fn($v, $k) => $v['date'] === $date->format('Y-m-d'),
                            ) ?? [
                                'status' => !$date->isPast() ? '-' : 'absent',
                            ])['status'];
                            switch ($status) {
                                case 'present':
                                    $shortStatus = 'H';
                                    $bgColor = 'status-h';
                                    $presentCount++;
                                    break;
                                case 'late':
                                    $shortStatus = 'T';
                                    $bgColor = 'status-t';
                                    $lateCount++;
                                    break;
                                case 'excused':
                                    $shortStatus = 'I';
                                    $bgColor = 'status-i';
                                    $excusedCount++;
                                    break;
                                case 'sick':
                                    $shortStatus = 'S';
                                    $bgColor = 'status-s';
                                    $sickCount++;
                                    break;
                                case 'absent':
                                    $shortStatus = 'A';
                                    $bgColor = 'status-a';
                                    $absentCount++;
                                    break;
                                case 'holiday':
                                    $shortStatus = 'L';
                                    $bgColor = 'status-l';
                                    $holidayCount++;
                                    break;
                                case 'lepas_jaga':
                                    $shortStatus = 'LJ';
                                    $bgColor = 'status-lj';
                                    $lepasJagaCount++;
                                    break;
                                default:
                                    $shortStatus = 'C';
                                    $bgColor = 'status-c';
                                    $cutiCount++;
                                    break;
                            }
                        @endphp
                        <td class="{{ $bgColor }}">{{ $shortStatus }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
