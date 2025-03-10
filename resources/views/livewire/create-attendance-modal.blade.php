<div>
    @if ($isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 sm:px-6">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity duration-300 ease-out"
                wire:click="closeModal"></div>

            <!-- Modal -->
            <div
                class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 sm:p-8 md:p-10 transform transition-all duration-300 ease-out scale-95 opacity-0 animate-modal">
                <h2 class="text-lg sm:text-xl font-bold mb-4 sm:mb-6 text-gray-800">Buat Absensi Manual</h2>
                <form wire:submit.prevent="save" class="space-y-4 sm:space-y-5">
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Pegawai</label>
                        <select wire:model="employee_id"
                            class="w-full border-gray-300 rounded-lg p-2 sm:p-3 focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Pegawai</option>
                            @forelse($employees ?? [] as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @empty
                                <option value="">Tidak ada pegawai</option>
                            @endforelse
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Shift</label>
                        <select wire:model="shift_id"
                            class="w-full border-gray-300 rounded-lg p-2 sm:p-3 focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Shift</option>
                            @forelse($shifts ?? [] as $data)
                                <option value="{{ $data->id }}">{{ $data->name }}</option>
                            @empty
                                <option value="">Tidak ada shift</option>
                            @endforelse
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Barcode</label>
                        <select wire:model="barcode_id"
                            class="w-full border-gray-300 rounded-lg p-2 sm:p-3 focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Barcode</option>
                            @forelse($barcode ?? [] as $data)
                                <option value="{{ $data->id }}">{{ $data->name }}</option>
                            @empty
                                <option value="">Tidak ada Barcode</option>
                            @endforelse
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Tanggal</label>
                        <input type="date" wire:model="date"
                            class="w-full border-gray-300 rounded-lg p-2 sm:p-3 focus:ring-2 focus:ring-blue-500" />
                    </div>
                    {{-- <div>
                        <label class="block text-gray-700 font-medium mb-1">Status</label>
                        <select wire:model="status"
                            class="w-full border-gray-300 rounded-lg p-2 sm:p-3 focus:ring-2 focus:ring-blue-500">
                            <option value="present">Hadir</option>
                            <option value="late">Terlambat</option>
                            <option value="sick">Sakit</option>
                            <option value="absent">Absen</option>
                            <option value="leave">Cuti</option>
                        </select>
                    </div> --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Waktu Masuk</label>
                            <input type="time" wire:model="time_in"
                                class="w-full border-gray-300 rounded-lg p-2 sm:p-3 focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Waktu Keluar</label>
                            <input type="time" wire:model="time_out"
                                class="w-full border-gray-300 rounded-lg p-2 sm:p-3 focus:ring-2 focus:ring-blue-500" />
                        </div>
                    </div>
                    <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
                        <button type="button" wire:click="closeModal"
                            class="px-4 py-2 sm:px-5 sm:py-2.5 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 sm:px-5 sm:py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <style>
        @keyframes modal-show {
            from {
                opacity: 0;
                transform: translateY(10px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .animate-modal {
            animation: modal-show 0.3s ease-out forwards;
        }
    </style>

</div>
