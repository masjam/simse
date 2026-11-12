<?php

namespace App\Imports;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class NilaiImport implements ToCollection, WithHeadingRow
{
    protected $teacher_id;
    protected $subject_name;

    public function __construct($teacher_id, $subject_name)
    {
        $this->teacher_id = $teacher_id;
        $this->subject_name = $subject_name;
    }

    public function collection(Collection $rows)
    {
        $subject = Subject::where('name', $this->subject_name)->first();
        if (!$subject) return;

        $fields = [
            't1' => ['type' => 'Tugas', 'name' => 'T1'],
            't2' => ['type' => 'Tugas', 'name' => 'T2'],
            't3' => ['type' => 'Tugas', 'name' => 'T3'],
            't4' => ['type' => 'Tugas', 'name' => 'T4'],
            'k1' => ['type' => 'Kuis', 'name' => 'K1'],
            'k2' => ['type' => 'Kuis', 'name' => 'K2'],
            'uts' => ['type' => 'UTS', 'name' => 'UTS'],
            'uas' => ['type' => 'UAS', 'name' => 'UAS'],
        ];

        foreach ($rows as $row) {
            if (!isset($row['nisn'])) continue;

            $student = Student::where('nisn', $row['nisn'])->first();
            if (!$student) continue;

            foreach ($fields as $col => $meta) {
                if (isset($row[$col]) && trim($row[$col]) !== '') {
                    Grade::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'subject_id' => $subject->id,
                            'teacher_id' => $this->teacher_id,
                            'type' => $meta['type'],
                            'name' => $meta['name'],
                        ],
                        [
                            'score' => $row[$col]
                        ]
                    );
                }
            }
        }
    }
}
