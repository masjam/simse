<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Indeks Komposit CBT (Exam Attempts) untuk 175 Siswa Ujian Serentak
        $this->addIndexSafely('exam_attempts', ['exam_id', 'student_id'], 'idx_exam_attempts_exam_student');
        $this->addIndexSafely('exam_attempts', ['exam_id', 'finished_at'], 'idx_exam_attempts_exam_finished');

        // 2. Indeks Soal & Pilihan Jawaban CBT
        $this->addIndexSafely('questions', ['exam_id', 'type'], 'idx_questions_exam_type');
        $this->addIndexSafely('options', ['question_id', 'is_correct'], 'idx_options_question_correct');

        // 3. Indeks Pelanggaran Anti-Cheat CBT
        if (Schema::hasTable('exam_violations')) {
            $this->addIndexSafely('exam_violations', ['exam_attempt_id', 'created_at'], 'idx_exam_violations_attempt_created');
        }

        // 4. Indeks Presensi Harian Siswa & Pegawai
        $this->addIndexSafely('attendances', ['user_id', 'date', 'status'], 'idx_attendances_user_date_status');

        // 5. Indeks Gamifikasi Siswa
        if (Schema::hasTable('point_events')) {
            $this->addIndexSafely('point_events', ['student_id', 'created_at'], 'idx_point_events_student_created');
        }

        // 6. Indeks Tagihan Keuangan Siswa
        $this->addIndexSafely('bills', ['student_id', 'status'], 'idx_bills_student_status');

        // 7. Indeks Kasbon & Angsuran Pegawai
        if (Schema::hasTable('advance_installments')) {
            $this->addIndexSafely('advance_installments', ['advance_id', 'status'], 'idx_advance_installments_advance_status');
        }
    }

    public function down(): void
    {
        $this->dropIndexSafely('exam_attempts', 'idx_exam_attempts_exam_student');
        $this->dropIndexSafely('exam_attempts', 'idx_exam_attempts_exam_finished');
        $this->dropIndexSafely('questions', 'idx_questions_exam_type');
        $this->dropIndexSafely('options', 'idx_options_question_correct');

        if (Schema::hasTable('exam_violations')) {
            $this->dropIndexSafely('exam_violations', 'idx_exam_violations_attempt_created');
        }

        $this->dropIndexSafely('attendances', 'idx_attendances_user_date_status');

        if (Schema::hasTable('point_events')) {
            $this->dropIndexSafely('point_events', 'idx_point_events_student_created');
        }

        $this->dropIndexSafely('bills', 'idx_bills_student_status');

        if (Schema::hasTable('advance_installments')) {
            $this->dropIndexSafely('advance_installments', 'idx_advance_installments_advance_status');
        }
    }

    private function addIndexSafely(string $table, array $columns, string $indexName): void
    {
        try {
            Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                $t->index($columns, $indexName);
            });
        } catch (Throwable $e) {
            // Abaikan jika indeks sudah ada
        }
    }

    private function dropIndexSafely(string $table, string $indexName): void
    {
        try {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropIndex($indexName);
            });
        } catch (Throwable $e) {
            // Abaikan jika indeks tidak ada
        }
    }
};
