<?php

namespace App\Exports;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class NilaiExport implements FromCollection, WithHeadings, WithMapping
{
    protected $class_name;
    protected $subject_name;
    protected $teacher_id;

    public function __construct($class_name, $subject_name, $teacher_id)
    {
        $this->class_name = $class_name;
        $this->subject_name = $subject_name;
        $this->teacher_id = $teacher_id;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $subject = Subject::where('name', $this->subject_name)->first();
        if (!$subject) return collect([]);

        $students = Student::whereHas('studentClass', function($q) {
            $q->where('name', $this->class_name);
        })->get();

        $grades = Grade::whereIn('student_id', $students->pluck('id'))
            ->where('subject_id', $subject->id)
            ->where('teacher_id', $this->teacher_id)
            ->get()
            ->groupBy('student_id');

        return $students->map(function ($student) use ($grades) {
            $studentGrades = collect($grades->get($student->id, []));
            
            $getGrade = function($type, $name) use ($studentGrades) {
                $g = $studentGrades->where('type', $type)->where('name', $name)->first();
                return $g ? $g->score : '';
            };

            return [
                'nisn' => $student->nisn,
                'nama_siswa' => $student->name,
                't1' => $getGrade('Tugas', 'T1'),
                't2' => $getGrade('Tugas', 'T2'),
                't3' => $getGrade('Tugas', 'T3'),
                't4' => $getGrade('Tugas', 'T4'),
                'k1' => $getGrade('Kuis', 'K1'),
                'k2' => $getGrade('Kuis', 'K2'),
                'uts' => $getGrade('UTS', 'UTS'),
                'uas' => $getGrade('UAS', 'UAS'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'NISN',
            'Nama Siswa',
            'T1',
            'T2',
            'T3',
            'T4',
            'K1',
            'K2',
            'UTS',
            'UAS',
        ];
    }

    public function map($row): array
    {
        return [
            $row['nisn'],
            $row['nama_siswa'],
            $row['t1'],
            $row['t2'],
            $row['t3'],
            $row['t4'],
            $row['k1'],
            $row['k2'],
            $row['uts'],
            $row['uas'],
        ];
    }
}
