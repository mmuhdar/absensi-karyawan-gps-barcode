<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Pengajuan Libur Baru
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    {{--  --}}
                    <div class="mb-4">
                        <x-secondary-button href="{{ url()->previous() }}">
                            <x-heroicon-o-chevron-left class="mr-2 h-5 w-5" />
                            Kembali
                        </x-secondary-button>
                    </div>
                    <form action="{{ route('store-holiday-request') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <div>
                                    <x-label for="status" value="{{ __('Status') }}" />
                                    <x-select id="status" class="mt-1 block w-full" name="status" required>
                                        <option value="holiday"
                                            {{ (old('status') ?? $attendance?->status) === 'holiday' ? 'selected' : '' }}>
                                            Libur
                                        </option>
                                        <option value="lepas_jaga"
                                            {{ (old('status') ?? $attendance?->status) === 'lepas_jaga' ? 'selected' : '' }}>
                                            Lepas Jaga
                                        </option>
                                    </x-select>
                                    @error('status')
                                        <x-input-error for="status" class="mt-2" message="{{ $message }}" />
                                    @enderror
                                </div>

                                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-3">
                                    <div>
                                        <x-label for="holidayDate" value="Tanggal Libur" />
                                        <x-input type="date" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}"
                                            id="holidayDate" class="mt-1 block w-full" name="holidayDate" required />
                                        @error('holidayDate')
                                            <x-input-error for="holidayDate" class="mt-2" message="{{ $message }}" />
                                        @enderror
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <x-label for="note" value="Keterangan" />
                                    <x-textarea id="note" type="text" class="mt-1 block w-full" name="note"
                                        required value="{{ old('note') ?? $attendance?->note }}" />
                                    <x-input-error for="note" class="mt-2" />
                                </div>
                            </div>
                        </div>
                </div>

                <input type="hidden" id="lat" name="lat" value="{{ $attendance?->latitude }}">
                <input type="hidden" id="lng" name="lng" value="{{ $attendance?->longitude }}">

                <div class="mb-3 mr-5 mt-4 flex items-center justify-end">
                    <x-button class="ms-4">
                        {{ __('Save') }}
                    </x-button>
                </div>
                </form>
                {{--  --}}
            </div>
        </div>
    </div>
    </div>
    @pushOnce('scripts')
        <script>
            getLocation();

            async function getLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.watchPosition((position) => {
                        console.log(position);
                        document.getElementById('lat').value = position.coords.latitude;
                        document.getElementById('lng').value = position.coords.longitude;
                    }, (err) => {
                        console.error(`ERROR(${err.code}): ${err.message}`);
                        alert('{{ __('Please enable your location') }}');
                    });
                }
            }
        </script>
    @endPushOnce
</x-app-layout>
