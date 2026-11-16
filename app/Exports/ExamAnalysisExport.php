<?php

namespace App\Exports;

use App\Models\Exam;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExamAnalysisExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        protected Exam $exam
    ) {}

    public function sheets(): array
    {
        return [
            new ExamStudentsScoresSheet($this->exam),
            new ExamItemAnalysisSheet($this->exam),
        ];
    }
}
