<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Migration bawaan Laravel ini DINONAKTIFKAN.
     * Tabel users, password_reset_tokens, dan sessions sudah didefinisikan
     * lengkap (dengan skema UUID custom) di:
     * 2026_09_24_070909_create_users_table.php
     */
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};