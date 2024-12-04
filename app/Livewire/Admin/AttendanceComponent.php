<?php

namespace App\Livewire\Admin;

use App\Livewire\Traits\AttendanceDetailTrait;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceComponent extends Component
{
    use AttendanceDetailTrait;
    use WithPagination, InteractsWithBanner;

    # filter
    public ?string $month;
    public ?string $week = null;
    public ?string $date = null;
    public ?string $division = null;
    public ?string $jobTitle = null;
    public ?string $search = null;

    public function mount()
    {
        $this->date = date('Y-m-d');
    }

    public function updating($key): void
    {
        if ($key === 'search' || $key === 'division' || $key === 'jobTitle') {
            $this->resetPage();
        }
        if ($key === 'month') {
            $this->resetPage();
            $this->week = null;
            $this->date = null;
        }
        if ($key === 'week') {
            $this->resetPage();
            $this->month = null;
            $this->date = null;
        }
        if ($key === 'date') {
            $this->resetPage();
            $this->month = null;
            $this->week = null;
        }
    }

    public function render()
    {
        if ($this->date) {
            $dates = [Carbon::parse($this->date)];
        } else if ($this->week) {
            $start = Carbon::parse($this->week)->startOfWeek();
            $end = Carbon::parse($this->week)->endOfWeek();
            $dates = $start->range($end)->toArray();
        } else if ($this->month) {
            $start = Carbon::parse($this->month)->startOfMonth();
            $end = Carbon::parse($this->month)->endOfMonth();
            $dates = $start->range($end)->toArray();
        }
        $employees = User::where('group', 'user')
            ->orderBy('name', 'asc')
            ->when($this->search, function (Builder $q) {
                return $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('nip', 'like', '%' . $this->search . '%');
            })
            ->when($this->division, fn(Builder $q) => $q->where('division_id', $this->division))
            ->when($this->jobTitle, fn(Builder $q) => $q->where('job_title_id', $this->jobTitle))
            ->paginate(20)
            ->through(function (User $user) {
                if ($this->date) {
                    // Check if cache exists
                    $cacheKey = "attendance-$user->id-$this->date";
                    if (Cache::has($cacheKey)) {
                        $attendances = new Collection(Cache::get($cacheKey));
                    } else {
                        /** @var Collection<Attendance> */
                        $attendances = Attendance::filter(
                            userId: $user->id,
                            date: $this->date,
                        )->get()->map(function (Attendance $v) {
                            $v->setAttribute('coordinates', $v->lat_lng);
                            $v->setAttribute('lat', $v->latitude);
                            $v->setAttribute('lng', $v->longitude);
                            if ($v->attachment) {
                                $v->setAttribute('attachment', $v->attachment_url);
                            }
                            if ($v->shift) {
                                $v->setAttribute('shift', $v->shift->name);
                            }
                            return $v->getAttributes();
                        });
                    }
                } else if ($this->week) {
                    $cacheKey = "attendance-$user->id-$this->week";
                    if (Cache::has($cacheKey)) {
                        $attendances = new Collection(Cache::get($cacheKey));
                    } else {
                        /** @var Collection<Attendance> */
                        $attendances = Attendance::filter(
                            userId: $user->id,
                            week: $this->week,
                        )->get(['id', 'status', 'date', 'latitude', 'longitude', 'attachment', 'note'])->map(function (Attendance $v) {
                            $v->setAttribute('coordinates', $v->lat_lng);
                            $v->setAttribute('lat', $v->latitude);
                            $v->setAttribute('lng', $v->longitude);
                            if ($v->attachment) {
                                $v->setAttribute('attachment', $v->attachment_url);
                            }
                            return $v->getAttributes();
                        });
                    }
                } else if ($this->month) {
                    $my = Carbon::parse($this->month);
                    $cacheKey = "attendance-$user->id-$my->month-$my->year";
                    if (Cache::has($cacheKey)) {
                        $attendances = new Collection(Cache::get($cacheKey));
                    } else {
                        /** @var Collection<Attendance> */
                        $attendances = Attendance::filter(
                            month: $this->month,
                            userId: $user->id,
                        )->get(['id', 'status', 'date', 'latitude', 'longitude', 'attachment', 'note'])->map(function (Attendance $v) {
                            $v->setAttribute('coordinates', $v->lat_lng);
                            $v->setAttribute('lat', $v->latitude);
                            $v->setAttribute('lng', $v->longitude);
                            if ($v->attachment) {
                                $v->setAttribute('attachment', $v->attachment_url);
                            }
                            return $v->getAttributes();
                        });
                    }
                } else {
                    /** @var Collection */
                    $attendances = Attendance::where('user_id', $user->id)
                        ->get(['id', 'status', 'date', 'latitude', 'longitude', 'attachment', 'note']);
                }

                $user->attendances = $attendances;
                return $user;
            });
        return view('livewire.admin.attendance', ['employees' => $employees, 'dates' => $dates]);
    }
}
