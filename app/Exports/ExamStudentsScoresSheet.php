<?php

namespace App\Exports;

use App\Models\Exam;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExamStudentsScoresSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(
        protected Exam $exam
    ) {}

    public function title(): string
    {
        return 'Rekap Nilai Siswa';
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Waktu Mulai',
            'Waktu Selesai',
            'Pelanggaran',
            'Status',
            'Nilai',
        ];
    }

    public function collection(): Collection
    {
        $this->exam->loadMissing([
            'attempts.student.user',
            'attempts.student.studentClass',
            'attempts.violations',
        ]);

        $data = [];
        $no = 1;

        foreach ($this->exam->attempts->sortByDesc('score') as $attempt) {
            $student = $attempt->student;
            $status = $attempt->finished_at ? ($attempt->is_forced_submit ? 'Selesai (Dipaksa)' : 'Selesai') : 'Mengerjakan';

            $data[] = [
                'no' => $no++,
                'nis' => $student?->nis ?? '-',
                'name' => $student?->user?->name ?? 'Siswa',
                'class' => $student?->studentClass?->name ?? '-',
                'started_at' => $attempt->started_at?->format('d/m/Y H:i') ?? '-',
                'finished_at' => $attempt->finished_at?->format('d/m/Y H:i') ?? '-',
                'violations' => $attempt->violations->count(),
                'status' => $status,
                'score' => $attempt->score !== null ? $attempt->score : 'Belum selesai',
            ];
        }

        return collect($data);
    }
}
