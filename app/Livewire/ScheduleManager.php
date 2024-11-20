<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Shift;
use App\Models\EmployeeSchedule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ScheduleManager extends Component
{
    public $month;
    public $year;
    public $dates = [];
    public $shifts = [];
    public $schedule = [];
    public $selectedMonth;
    public $selectedYear;
    // public $nowSchedule;

    public function mount()
    {
        // Set default month and year based on current date
        $this->month = now()->format('m');
        $this->year = now()->format('Y');
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
        // $this->nowSchedule = EmployeeSchedule::with('shift.attendances')->where('date', now()->format('Y-m-d'))->first();

        // Load shifts and schedules based on the current month and year
        $this->loadShiftsAndSchedules();
    }

    // Function to load shifts and schedules based on selected month and year
    public function loadShiftsAndSchedules()
    {
        // Clear previous data
        $this->dates = [];
        $this->shifts = Shift::all(); // Get all shifts

        // Generate list of dates for the selected month and year
        $daysInMonth = Carbon::create($this->year, $this->month, 1)->daysInMonth;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $this->dates[] = Carbon::create($this->year, $this->month, $day)->toDateString();
        }

        // Get the employee schedule from the database for the selected month and year
        $userId = Auth::id();
        $schedules = EmployeeSchedule::where('user_id', $userId)
            ->whereYear('date', $this->year)
            ->whereMonth('date', $this->month)
            ->get()
            ->keyBy('date'); // Use date as key for easier lookup

        // Fill schedule array based on the data from the database
        foreach ($this->dates as $date) {
            $formattedDate = Carbon::parse($date)->format('Y-m-d');
            $this->schedule[$formattedDate] = isset($schedules[$formattedDate]) ? $schedules[$formattedDate]->shift_id : '';
        }
    }

    // Called when selectedMonth changes
    public function updatedSelectedMonth()
    {
        $this->month = $this->selectedMonth; // Update month
        $this->loadShiftsAndSchedules(); // Reload shifts and schedules
    }

    // Called when selectedYear changes
    public function updatedSelectedYear()
    {
        $this->year = $this->selectedYear; // Update year
        $this->loadShiftsAndSchedules(); // Reload shifts and schedules
    }

    // Function to save selected schedules
    public function save()
    {
        $userId = Auth::id();

        foreach ($this->schedule as $date => $shiftId) {
            if ($shiftId) {
                // Save or update the employee schedule for the selected date
                EmployeeSchedule::updateOrCreate(
                    ['user_id' => $userId, 'date' => $date],
                    ['shift_id' => $shiftId]
                );
            }
        }

        session()->flash('message', 'Jadwal berhasil disimpan!');
    }

    // Render the view
    public function render()
    {
        return view('livewire.schedule-manager');
    }
}
