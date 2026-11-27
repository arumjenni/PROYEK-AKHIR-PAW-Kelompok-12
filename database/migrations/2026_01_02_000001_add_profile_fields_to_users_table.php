<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik', 20)->nullable()->unique()->after('phone');          // warga
            $table->string('badge_number', 30)->nullable()->after('nik');             // petugas (NRP)
            $table->string('unit')->nullable()->after('badge_number');                // petugas (satuan)
            $table->string('shift_start', 5)->nullable()->after('unit');              // "08:00"
            $table->string('shift_end', 5)->nullable()->after('shift_start');         // "16:00"
            $table->boolean('is_active')->default(true)->after('role');               // akun nonaktif tidak bisa login
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nik']);
            $table->dropColumn(['nik', 'badge_number', 'unit', 'shift_start', 'shift_end', 'is_active']);
        });
    }
};
