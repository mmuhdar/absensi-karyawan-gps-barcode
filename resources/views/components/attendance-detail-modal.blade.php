<x-modal wire:model="showDetail" onclose="removeMap()">
    <div class="px-6 py-4">
        @if ($currentAttendance)
            @php
                $isExcused = $currentAttendance['status'] == 'excused' || $currentAttendance['status'] == 'sick';
                $showMap = $currentAttendance['latitude'] && $currentAttendance['longitude'] && !$isExcused;
                $statuses = [
                    ['value' => 'present', 'label' => 'Hadir'],
                    ['value' => 'late', 'label' => 'Terlambat'],
                    ['value' => 'excused', 'label' => 'Izin'],
                    ['value' => 'sick', 'label' => 'Sakit'],
                    ['value' => 'absent', 'label' => 'Tidak Hadir'],
                    ['value' => 'holiday', 'label' => 'Libur'],
                    ['value' => 'cuti', 'label' => 'Cuti'],
                    ['value' => 'lepas_jaga', 'label' => 'Lepas Jaga'],
                    ['value' => 'dinas_luar', 'label' => 'Dinas Luar'],
                ];
            @endphp
            <h3 class="mb-3 text-xl font-semibold dark:text-white">{{ $currentAttendance['name'] }}</h3>
            {{-- <div class="mb-3 w-full">
                <x-label for="nip" value="{{ __('NIP') }}"></x-label>
                <x-input type="text" class="w-full" id="nip" disabled
                    value="{{ $currentAttendance['nip'] }}"></x-input>
            </div> --}}
            <div class="mb-3 flex w-full gap-3">
                <div class="w-full">
                    <x-label for="date" value="{{ __('Date') }}"></x-label>
                    <x-input type="date" class="w-full" id="date"
                        value="{{ $currentAttendance['date'] }}"></x-input>
                </div>
                <div class="w-full">
                    <x-label for="status" value="{{ __('Status') }}"></x-label>
                    <x-select class="w-full" id="status" value="{{ __($currentAttendance['status']) }}">
                        @foreach ($statuses as $status)
                            <option value="{{ $status['value'] }}"
                                {{ $currentAttendance['status'] === $status['value'] ? 'selected' : '' }}>
                                {{ $status['label'] }}
                            </option>
                        @endforeach
                    </x-select>
                </div>
            </div>
            @if ($isExcused)
                <div class="mb-3 w-full">
                    <x-label for="address" value="{{ __('Address') }}" />
                    <x-input type="text" class="w-full" id="address" disabled
                        value="{{ $currentAttendance['address'] }}" />
                </div>
            @endif
            <div class="flex flex-col gap-3">
                @if ($currentAttendance['attachment'])
                    <x-label for="attachment" value="{{ __('Attachment') }}"></x-label>
                    <img src="{{ $currentAttendance['attachment'] }}" alt="Attachment"
                        class="max-h-48 object-contain sm:max-h-64 md:max-h-72">
                @endif
                @if ($currentAttendance['note'])
                    <x-label for="note" value="Keterangan" />
                    <x-textarea type="text" id="note" disabled value="{{ $currentAttendance['note'] }}" />
                @endif
                @if ($showMap)
                    <x-label for="map" value="Koordinat Lokasi Absen"></x-label>
                    <p class="dark:text-gray-300">
                        {{ $currentAttendance['latitude'] }}, {{ $currentAttendance['longitude'] }}
                    </p>
                    <div class="my-2 h-52 w-full md:h-64" id="map"></div>
                @endif
                @if ($currentAttendance['time_in'] || $currentAttendance['time_out'])
                    <div class="grid grid-cols-2 gap-3">
                        <div class="w-full">
                            <x-label for="time_in" value="{{ __('Time In') }}"></x-label>
                            <x-input type="time" class="w-full" id="time_in"
                                value="{{ $currentAttendance['time_in'] }}"></x-input>
                        </div>
                        <div class="w-full">
                            <x-label for="time_out" value="{{ __('Time Out') }}"></x-label>
                            <x-input type="time" class="w-full" id="time_out"
                                value="{{ $currentAttendance['time_out'] }}"></x-input>
                        </div>
                    </div>
                @endif

                <div class="flex gap-3">
                    @if ($currentAttendance['shift'] ?? false)
                        <div class="w-full">
                            <x-label for="shift" value="Shift"></x-label>
                            <x-input class="w-full" type="text" id="shift" disabled
                                value="{{ $currentAttendance['shift']['name'] }}"></x-input>
                        </div>
                    @endif
                    @if ($currentAttendance['barcode'] ?? false)
                        <div class="w-full">
                            <x-label for="barcode" value="Barcode"></x-label>
                            <x-input class="w-full" type="text" id="barcode" disabled
                                value="{{ $currentAttendance['barcode']['name'] }}"></x-input>
                        </div>
                    @endif
                </div>
                <input type="hidden" id="attendance-id" value="{{ $currentAttendance['id'] }}">
            </div>
        @endif
        <div class="mt-20 flex gap-5">
            {{-- <x-prime-button onclick="updateAttendance()">Perbarui</x-prime-button> --}}
            <x-danger-button onclick="deleteAttendance()">Hapus</x-danger-button>
        </div>
    </div>
</x-modal>

@push('attendance-detail-scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        let map = null;

        function updateAttendance() {
            const id = document.getElementById('attendance-id').value;
            const data = {
                date: document.getElementById('date').value,
                status: document.getElementById('status').value,
                note: "{{ $currentAttendance['note'] ?? '' }}",
            };

            fetch(`/admin/attendance/${id}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(data),
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    location.reload(); // Reload page to reflect changes
                })
                .catch(error => console.error('Error:', error));
        }

        function deleteAttendance() {
            const id = document.getElementById('attendance-id').value;

            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                fetch(`/admin/attendance/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        location.reload(); // Reload page to reflect changes
                    })
                    .catch(error => console.error('Error:', error));
            }
        }

        function setLocation(lat, lng) {
            removeMap();
            setTimeout(() => {
                map = L.map('map').setView([Number(lat), Number(lng)], 19);
                L.marker([Number(lat), Number(lng)]).addTo(map);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 21,
                }).addTo(map);
            }, 500);
        }

        function removeMap() {
            if (map !== null) map.remove();
            map = null;
        }
    </script>
@endpush
