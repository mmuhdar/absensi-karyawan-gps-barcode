<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Menambahkan nilai enum baru pada kolom 'status' di tabel 'attendances'
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'late', 'excused', 'sick', 'absent', 'holiday', 'cuti', 'lepas_jaga', 'dinas_luar') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'late', 'excused', 'sick', 'absent') NOT NULL");
    }
};
