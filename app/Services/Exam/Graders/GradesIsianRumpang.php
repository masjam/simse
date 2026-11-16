<?php

namespace App\Services\Exam\Graders;

use App\Models\Exam;
use App\Models\Question;
use App\Services\Exam\Contracts\QuestionGraderInterface;

class GradesIsianRumpang implements QuestionGraderInterface
{
    public function grade(Question $question, mixed $answer, ?Exam $exam = null, array $context = []): array
    {
        $maxMarks = (float) ($question->marks ?: 10);

        // Normalize student answers into array indexed by blank index
        $studentBlanks = [];
        if (is_array($answer)) {
            if (isset($answer['blanks']) && is_array($answer['blanks'])) {
                $studentBlanks = $answer['blanks'];
            } elseif (isset($answer['text_answers']) && is_array($answer['text_answers'])) {
                $studentBlanks = $answer['text_answers'];
            } elseif (isset($answer['text_answer'])) {
                $studentBlanks = [$answer['text_answer']];
            } else {
                $studentBlanks = $answer;
            }
        } elseif (is_string($answer) || is_numeric($answer)) {
            $studentBlanks = [(string) $answer];
        }

        // Retrieve blanks definition from metadata or options
        $blanksDef = $this->extractBlanksDefinition($question);
        $totalBlanks = count($blanksDef);

        if ($totalBlanks === 0) {
            return [
                'earned_marks' => 0.0,
                'max_marks' => $maxMarks,
                'is_correct' => false,
                'needs_manual' => false,
                'feedback' => 'Belum ada kunci jawaban rumpang yang dikonfigurasi',
            ];
        }

        if (empty($studentBlanks)) {
            return [
                'earned_marks' => 0.0,
                'max_marks' => $maxMarks,
                'is_correct' => false,
                'needs_manual' => false,
                'feedback' => 'Semua isian rumpang kosong',
            ];
        }

        $correctCount = 0;
        foreach ($blanksDef as $index => $blankInfo) {
            $studentInput = $studentBlanks[$index] ?? $studentBlanks[(string) $index] ?? null;

            if ($studentInput === null || trim((string) $studentInput) === '') {
                continue;
            }

            $acceptedList = $blankInfo['accepted'] ?? [];
            $ignorePunct = $blankInfo['ignore_punct'] ?? true;

            $isMatch = false;
            foreach ($acceptedList as $target) {
                if ($this->compareAnswers((string) $studentInput, (string) $target, $ignorePunct)) {
                    $isMatch = true;
                    break;
                }
            }

            if ($isMatch) {
                $correctCount++;
            }
        }

        $earned = round(($correctCount / $totalBlanks) * $maxMarks, 2);
        $isExact = ($correctCount === $totalBlanks);

        return [
            'earned_marks' => $earned,
            'max_marks' => $maxMarks,
            'is_correct' => $isExact,
            'needs_manual' => false,
            'feedback' => "Tepat mengisi {$correctCount} dari {$totalBlanks} isian rumpang",
        ];
    }

    /**
     * Extract array of blanks from metadata['blanks'] or fallback to options.
     *
     * @return array<int, array{accepted: array<string>, ignore_punct: bool}>
     */
    private function extractBlanksDefinition(Question $question): array
    {
        $meta = $question->metadata;
        if (! empty($meta['blanks']) && is_array($meta['blanks'])) {
            $result = [];
            foreach ($meta['blanks'] as $idx => $item) {
                $accepted = [];
                if (isset($item['accepted']) && is_array($item['accepted'])) {
                    $accepted = $item['accepted'];
                } elseif (isset($item['key'])) {
                    $accepted = array_map('trim', explode('|', (string) $item['key']));
                } elseif (is_string($item)) {
                    $accepted = array_map('trim', explode('|', $item));
                }

                $result[$idx] = [
                    'accepted' => array_filter($accepted, fn ($v) => $v !== ''),
                    'ignore_punct' => $item['ignore_punct'] ?? true,
                ];
            }

            return $result;
        }

        // Fallback: options table where each option is a blank
        if ($question->options->isNotEmpty()) {
            $result = [];
            foreach ($question->options->values() as $idx => $opt) {
                $accepted = array_map('trim', preg_split('/[|,]/', $opt->content));
                $result[$idx] = [
                    'accepted' => array_filter($accepted, fn ($v) => $v !== ''),
                    'ignore_punct' => true,
                ];
            }

            return $result;
        }

        return [];
    }

    /**
     * Compare student answer with accepted key with text normalization & numeric tolerance.
     */
    public function compareAnswers(string $student, string $accepted, bool $ignorePunct = true): bool
    {
        $sNorm = $this->normalizeText($student, $ignorePunct);
        $aNorm = $this->normalizeText($accepted, $ignorePunct);

        // Numeric comparison (e.g. 2,5 == 2.5 or 05 == 5)
        $sNumeric = $this->parseNumeric($sNorm);
        $aNumeric = $this->parseNumeric($aNorm);

        if ($sNumeric !== null && $aNumeric !== null) {
            return abs($sNumeric - $aNumeric) < 0.0001;
        }

        return $sNorm === $aNorm;
    }

    private function normalizeText(string $text, bool $ignorePunct): string
    {
        $str = trim($text);
        $str = mb_strtolower($str, 'UTF-8');

        // Replace comma between digits with dot (Indonesian decimal standard e.g. 3,14 -> 3.14)
        $str = preg_replace('/(\d+),(\d+)/', '$1.$2', $str);

        if ($ignorePunct) {
            // Strip punctuation, keep letters, digits, whitespace, and decimal dot
            $str = preg_replace('/[^\p{L}\p{N}\s.]/u', '', $str);
        }

        // Collapse duplicate whitespace
        $str = preg_replace('/\s+/', ' ', $str);

        return trim($str);
    }

    private function parseNumeric(string $str): ?float
    {
        if (is_numeric($str)) {
            return (float) $str;
        }

        return null;
    }
}
