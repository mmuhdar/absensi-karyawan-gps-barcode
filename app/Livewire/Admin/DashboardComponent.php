<?php

namespace App\Livewire\Admin;

use App\Livewire\Traits\AttendanceDetailTrait;
use App\Models\Attendance;
use App\Models\EmployeeSchedule;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class DashboardComponent extends Component
{
    use AttendanceDetailTrait;

    public function render()
    {
        /** @var Collection<Attendance>  */
        $attendances = Attendance::with('shift')->where('date', date('Y-m-d'))->get();

        /** @var Collection<User>  */
        $employees = User::where('group', 'user')
            ->orderBy('name', 'asc')
            ->paginate(20)
            ->through(function (User $user) use ($attendances) {
                return $user->setAttribute(
                    'attendance',
                    $attendances
                        ->where(fn(Attendance $attendance) => $attendance->user_id === $user->id)
                        ->first(),
                );
            });

        $employeesCount = User::where('group', 'user')->count();
        $presentCount = $attendances->where(fn($attendance) => $attendance->status === 'present')->count();
        $lateCount = $attendances->where(fn($attendance) => $attendance->status === 'late')->count();
        $excusedCount = $attendances->where(fn($attendance) => $attendance->status === 'excused')->count();
        $sickCount = $attendances->where(fn($attendance) => $attendance->status === 'sick')->count();
        // $holidayCount = EmployeeSchedule::where('date', date('Y-m-d'))->whereHas('shift', function ($query) {
        //     return $query->where('name', 'Libur');
        // })->count();
        $holidayCount = $attendances->where(fn($attendance) => $attendance->status === 'holiday')->count();
        $cutiCount = $attendances->where(fn($attendance) => $attendance->status === 'cuti')->count();
        $dinasCount = $attendances->where(fn($attendance) => $attendance->status === 'dinas_luar')->count();
        $lepasJagaCount = $attendances->where(fn($attendance) => $attendance->status === 'lepas_jaga')->count();
        $totalHolidayCount = $holidayCount + $lepasJagaCount;
        $absentCount = $employeesCount - ($presentCount + $lateCount + $excusedCount + $sickCount + $totalHolidayCount + $cutiCount + $dinasCount);

        return view('livewire.admin.dashboard', [
            'employees' => $employees,
            'employeesCount' => $employeesCount,
            'presentCount' => $presentCount,
            'lateCount' => $lateCount,
            'excusedCount' => $excusedCount,
            'sickCount' => $sickCount,
            'absentCount' => $absentCount,
            'cutiCount' => $cutiCount,
            'dinasCount' => $dinasCount,
            'lepasJagaCount' => $lepasJagaCount,
            'holidayCount' => $holidayCount,
        ]);
    }
}
