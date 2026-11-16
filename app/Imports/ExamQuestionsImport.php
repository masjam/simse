<?php

namespace App\Imports;

use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExamQuestionsImport implements ToCollection, WithHeadingRow
{
    protected array $importErrors = [];

    protected int $importedCount = 0;

    public function __construct(
        protected Exam $exam
    ) {}

    public function collection(Collection $rows): void
    {
        $validTypes = ['Pilihan Ganda', 'Ganda Kompleks', 'Benar Salah', 'Menjodohkan', 'Isian Rumpang', 'Esai'];

        DB::transaction(function () use ($rows, $validTypes) {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // Accounting for 1-based index and heading row

                $type = trim($row['tipe_soal'] ?? '');
                $content = trim($row['soal'] ?? '');
                $marks = (int) ($row['bobot'] ?? 10);
                $key = trim((string) ($row['kunci_jawaban'] ?? ''));

                if ($content === '' && $type === '') {
                    continue; // Skip empty rows
                }

                if (! in_array($type, $validTypes, true)) {
                    $this->importErrors[] = "Baris {$rowNumber}: Tipe soal [{$type}] tidak valid. Gunakan: ".implode(', ', $validTypes);

                    continue;
                }

                if ($content === '') {
                    $this->importErrors[] = "Baris {$rowNumber}: Teks soal tidak boleh kosong.";

                    continue;
                }

                if ($marks <= 0) {
                    $marks = 10;
                }

                // Process per question type
                $this->processRow($rowNumber, $type, $content, $marks, $key, $row);
            }
        });
    }

    protected function processRow(int $rowNumber, string $type, string $content, int $marks, string $key, $row): void
    {
        $metadata = [];

        if ($type === 'Isian Rumpang') {
            $acceptedList = array_filter(array_map('trim', preg_split('/[|,]/', $key)), fn ($v) => $v !== '');
            if (empty($acceptedList)) {
                $this->importErrors[] = "Baris {$rowNumber}: Isian Rumpang wajib memiliki kunci jawaban (pisahkan dengan tanda | jika ada alternatif).";

                return;
            }

            $metadata['blanks'] = [
                0 => [
                    'accepted' => array_values($acceptedList),
                    'ignore_punct' => true,
                ],
            ];

            $question = Question::create([
                'exam_id' => $this->exam->id,
                'type' => $type,
                'content' => $content,
                'marks' => $marks,
                'metadata' => $metadata,
            ]);

            $this->importedCount++;

            return;
        }

        if ($type === 'Esai') {
            Question::create([
                'exam_id' => $this->exam->id,
                'type' => $type,
                'content' => $content,
                'marks' => $marks,
            ]);

            $this->importedCount++;

            return;
        }

        if ($type === 'Menjodohkan') {
            // Key format: "Premis 1:Pasangan 1|Premis 2:Pasangan 2"
            $pairItems = array_filter(array_map('trim', explode('|', $key)), fn ($v) => $v !== '');
            if (empty($pairItems)) {
                $this->importErrors[] = "Baris {$rowNumber}: Menjodohkan memerlukan format kunci 'Premis:Pasangan' dipisahkan dengan tanda '|'.";

                return;
            }

            $question = Question::create([
                'exam_id' => $this->exam->id,
                'type' => $type,
                'content' => $content,
                'marks' => $marks,
            ]);

            foreach ($pairItems as $pairStr) {
                $parts = explode(':', $pairStr, 2);
                $left = trim($parts[0] ?? '');
                $right = trim($parts[1] ?? '');

                if ($left !== '' && $right !== '') {
                    Option::create([
                        'question_id' => $question->id,
                        'content' => $left,
                        'pair_match' => $right,
                        'is_correct' => true,
                    ]);
                }
            }

            $this->importedCount++;

            return;
        }

        if ($type === 'Benar Salah') {
            $normalizedKey = mb_strtolower($key);
            $isCorrectTrue = in_array($normalizedKey, ['benar', 'b', 'true', '1'], true);

            $question = Question::create([
                'exam_id' => $this->exam->id,
                'type' => $type,
                'content' => $content,
                'marks' => $marks,
            ]);

            Option::create([
                'question_id' => $question->id,
                'content' => 'Benar',
                'is_correct' => $isCorrectTrue,
            ]);

            Option::create([
                'question_id' => $question->id,
                'content' => 'Salah',
                'is_correct' => ! $isCorrectTrue,
            ]);

            $this->importedCount++;

            return;
        }

        // Pilihan Ganda & Ganda Kompleks
        $optionsRaw = [
            'A' => trim($row['opsi_a'] ?? ''),
            'B' => trim($row['opsi_b'] ?? ''),
            'C' => trim($row['opsi_c'] ?? ''),
            'D' => trim($row['opsi_d'] ?? ''),
        ];

        $availableOptions = array_filter($optionsRaw, fn ($v) => $v !== '');
        if (count($availableOptions) < 2) {
            $this->importErrors[] = "Baris {$rowNumber}: Pilihan Ganda minimal harus memiliki opsi A dan B.";

            return;
        }

        $correctKeys = array_map('trim', array_map('strtoupper', preg_split('/[,;|]/', $key)));
        $correctKeys = array_filter($correctKeys, fn ($k) => isset($availableOptions[$k]));

        if (empty($correctKeys)) {
            $this->importErrors[] = "Baris {$rowNumber}: Kunci jawaban [{$key}] tidak cocok dengan opsi A/B/C/D yang tersedia.";

            return;
        }

        $question = Question::create([
            'exam_id' => $this->exam->id,
            'type' => $type,
            'content' => $content,
            'marks' => $marks,
        ]);

        foreach ($availableOptions as $optLetter => $optContent) {
            Option::create([
                'question_id' => $question->id,
                'content' => $optContent,
                'is_correct' => in_array($optLetter, $correctKeys, true),
            ]);
        }

        $this->importedCount++;
    }

    public function getErrors(): array
    {
        return $this->importErrors;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }
}
