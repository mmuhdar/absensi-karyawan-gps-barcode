<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Barcode;
use App\Models\Shift;
use Livewire\Attributes\On;

class CreateAttendanceModal extends Component
{
    public $isOpen = false;
    public $employee_id, $date, $status, $time_in, $time_out, $shift_id, $barcode_id;
    public $employees, $shifts, $barcode;

    protected $rules = [
        'employee_id' => 'required',
        'date' => 'required|date',
        'time_in' => 'nullable|date_format:H:i',
        'time_out' => 'nullable|date_format:H:i|after:time_in',
    ];

    // public function mount()
    // {
    //     $this->employees = User::all();
    // }

    #[On('openAttendanceModal')]
    public function openModal()
    {
        $this->reset();
        $this->employees = User::all()->where('group', 'user');
        $this->shifts = Shift::all();
        $this->barcode = Barcode::all();
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    public function save()
    {
        $this->validate();

        Attendance::create([
            'user_id' => $this->employee_id,
            'shift_id' => $this->shift_id,
            'barcode_id' => $this->barcode_id,
            'latitude' => 1.0354721102108,
            'longitude' => 120.82266927373,
            'date' => $this->date,
            'status' => "present",
            'time_in' => $this->time_in,
            'time_out' => $this->time_out,
        ]);

        session()->flash('message', 'Absensi berhasil ditambahkan.');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.create-attendance-modal');
    }
}
