<?php

namespace App\Livewire;

use App\ExtendedCarbon;
use App\Models\Attendance;
use App\Models\Barcode;
use App\Models\EmployeeSchedule;
use App\Models\Shift;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Ballen\Distical\Calculator as DistanceCalculator;
use Ballen\Distical\Entities\LatLong;
use Illuminate\Support\Carbon;

class ScanComponent extends Component
{
    public ?Attendance $attendance = null;
    public $shift_id = 0;
    public $shifts = null;
    public ?array $currentLiveCoords = null;
    public string $successMsg = '';
    public string $errorMsg = '';
    public bool $isAbsence = false;
    public $nowSchedule;

    public function scan(string $barcode)
    {
        if (is_null($this->currentLiveCoords)) {
            return __('Invalid location');
        } else if (is_null($this->shift_id)) {
            return __('Invalid shift');
        }

        $now = Carbon::now();
        $allowedScanTime = Carbon::createFromFormat('H:i:s', $this->nowSchedule->shift->start_time)->subHour();

        if ($now->lt($allowedScanTime)) {
            // return __('You cannot scan earlier than 1 hour before the shift starts.');
            return __('Anda tidak bisa scan lebih awal dari 1 jam sebelum shift dimulai.');
        }

        /** @var Barcode */
        $barcode = Barcode::firstWhere('value', $barcode);
        if (!Auth::check() || !$barcode) {
            return 'Invalid barcode';
        }

        $barcodeLocation = new LatLong($barcode->latLng['lat'], $barcode->latLng['lng']);
        $userLocation = new LatLong($this->currentLiveCoords[0], $this->currentLiveCoords[1]);

        if (($distance = $this->calculateDistance($userLocation, $barcodeLocation)) > $barcode->radius) {
            return __('Location out of range') . ": $distance" . "m. Max: $barcode->radius" . "m";
        }

        /** @var Attendance */
        $existingAttendance = Attendance::where('user_id', Auth::user()->id)
            ->where('date', date('Y-m-d'))
            ->where('barcode_id', $barcode->id)
            ->first();

        if (!$existingAttendance) {
            $attendance = $this->createAttendance($barcode);
            $this->successMsg = __('Attendance In Successful');
        } else {
            $attendance = $existingAttendance;
            $attendance->update([
                'time_out' => date('H:i:s'),
            ]);
            $this->successMsg = __('Attendance Out Successful');
        }

        if ($attendance) {
            $this->setAttendance($attendance->fresh());
            Attendance::clearUserAttendanceCache(Auth::user(), Carbon::parse($attendance->date));
            return true;
        }
    }

    public function calculateDistance(LatLong $a, LatLong $b)
    {
        $distanceCalculator = new DistanceCalculator($a, $b);
        $distanceInMeter = floor($distanceCalculator->get()->asKilometres() * 1000); // convert to meters
        return $distanceInMeter;
    }

    /** @return Attendance */
    public function createAttendance(Barcode $barcode)
    {
        $now = Carbon::now();
        $date = $now->format('Y-m-d');
        $timeIn = $now->format('H:i:s');
        /** @var Shift */
        $shift = Shift::find($this->shift_id);
        $scheduleId = $this->nowSchedule->id;


        if (!$this->shift_id) {
            $this->errorMsg = 'Maaf anda belum melakukan penjadwalan shift untuk hari ini.';
            return;
        }

        $status = Carbon::now()->setTimeFromTimeString($shift->start_time)->addMinutes(60)->lt($now) ? 'late' : 'present';
        return Attendance::create([
            'user_id' => Auth::user()->id,
            'barcode_id' => $barcode->id,
            'date' => $date,
            'time_in' => $timeIn,
            'time_out' => null,
            'shift_id' => $shift->id,
            'schedule_id' => $scheduleId,
            'latitude' => doubleval($this->currentLiveCoords[0]),
            'longitude' => doubleval($this->currentLiveCoords[1]),
            'status' => $status,
            'note' => null,
            'attachment' => null,
        ]);
    }

    protected function setAttendance(Attendance $attendance)
    {
        $this->attendance = $attendance;
        $this->shift_id = $attendance->shift_id;
        $this->isAbsence = $attendance->status !== 'present' && $attendance->status !== 'late';
    }

    public function getAttendance()
    {
        if (is_null($this->attendance)) {
            return null;
        }
        return [
            'time_in' => $this->attendance?->time_in,
            'time_out' => $this->attendance?->time_out,
        ];
    }

    public function mount()
    {
        $this->shifts = Shift::all();
        $this->nowSchedule = EmployeeSchedule::with('shift.attendances')->where('user_id', Auth::user()->id)
            ->where('date', Carbon::today()->format('Y-m-d'))->first();
        $this->shift_id = $this->nowSchedule->shift->id ?? 0;

        /** @var Attendance */
        $attendance = Attendance::where('user_id', Auth::user()->id)
            ->where('date', date('Y-m-d'))->first();
        if ($attendance) {
            $this->setAttendance($attendance);
        }
    }

    public function render()
    {
        return view('livewire.scan');
    }
}
