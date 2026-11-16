<?php

namespace Tests\Unit;

use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Services\Exam\ExamGrader;
use App\Services\Exam\Graders\GradesBenarSalah;
use App\Services\Exam\Graders\GradesEsai;
use App\Services\Exam\Graders\GradesGandaKompleks;
use App\Services\Exam\Graders\GradesIsianRumpang;
use App\Services\Exam\Graders\GradesMenjodohkan;
use App\Services\Exam\Graders\GradesPilihanGanda;
use Tests\TestCase;

class ExamGraderTest extends TestCase
{
    private ExamGrader $grader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->grader = new ExamGrader;
    }

    public function test_pilihan_ganda_grading(): void
    {
        $q = (new Question)->forceFill(['marks' => 20]);
        $optA = (new Option)->forceFill(['id' => 'opt-1', 'content' => 'A', 'is_correct' => false]);
        $optB = (new Option)->forceFill(['id' => 'opt-2', 'content' => 'B', 'is_correct' => true]);
        $q->setRelation('options', collect([$optA, $optB]));

        $grader = new GradesPilihanGanda;

        // Correct
        $res = $grader->grade($q, ['option_id' => 'opt-2']);
        $this->assertEquals(20.0, $res['earned_marks']);
        $this->assertTrue($res['is_correct']);

        // Wrong
        $resWrong = $grader->grade($q, ['option_id' => 'opt-1']);
        $this->assertEquals(0.0, $resWrong['earned_marks']);
        $this->assertFalse($resWrong['is_correct']);

        // Unanswered
        $resEmpty = $grader->grade($q, null);
        $this->assertEquals(0.0, $resEmpty['earned_marks']);
    }

    public function test_ganda_kompleks_standard_rule(): void
    {
        $exam = (new Exam)->forceFill(['scoring_rule' => 'standard']);
        $q = (new Question)->forceFill(['marks' => 30]);

        $optA = (new Option)->forceFill(['id' => 'k1', 'is_correct' => true]);
        $optB = (new Option)->forceFill(['id' => 'k2', 'is_correct' => true]);
        $optC = (new Option)->forceFill(['id' => 'w1', 'is_correct' => false]);
        $q->setRelation('options', collect([$optA, $optB, $optC]));

        $grader = new GradesGandaKompleks;

        // All correct chosen
        $res = $grader->grade($q, ['option_ids' => ['k1', 'k2']], $exam);
        $this->assertEquals(30.0, $res['earned_marks']);
        $this->assertTrue($res['is_correct']);

        // Missing one correct
        $resMissing = $grader->grade($q, ['option_ids' => ['k1']], $exam);
        $this->assertEquals(0.0, $resMissing['earned_marks']);
        $this->assertFalse($resMissing['is_correct']);

        // Extra wrong chosen
        $resExtra = $grader->grade($q, ['option_ids' => ['k1', 'k2', 'w1']], $exam);
        $this->assertEquals(0.0, $resExtra['earned_marks']);
    }

    public function test_ganda_kompleks_partial_rule_with_penalty(): void
    {
        $exam = (new Exam)->forceFill(['scoring_rule' => 'partial']);
        $q = (new Question)->forceFill(['marks' => 20]); // 2 correct keys: each worth 10 marks

        $opt1 = (new Option)->forceFill(['id' => 'k1', 'is_correct' => true]);
        $opt2 = (new Option)->forceFill(['id' => 'k2', 'is_correct' => true]);
        $opt3 = (new Option)->forceFill(['id' => 'w1', 'is_correct' => false]);
        $q->setRelation('options', collect([$opt1, $opt2, $opt3]));

        $grader = new GradesGandaKompleks;

        // Student chose 1 correct out of 2: ratio 1/2 = 10 marks
        $res = $grader->grade($q, ['option_ids' => ['k1']], $exam);
        $this->assertEquals(10.0, $res['earned_marks']);

        // Student chose 1 correct and 1 incorrect: (1 - 1)/2 = 0 marks
        $resPenalized = $grader->grade($q, ['option_ids' => ['k1', 'w1']], $exam);
        $this->assertEquals(0.0, $resPenalized['earned_marks']);

        // Student chose only incorrect: 0 marks (not negative)
        $resNegative = $grader->grade($q, ['option_ids' => ['w1']], $exam);
        $this->assertEquals(0.0, $resNegative['earned_marks']);
    }

    public function test_benar_salah_grading(): void
    {
        $q = (new Question)->forceFill(['marks' => 10]);
        $optTrue = (new Option)->forceFill(['id' => 'b1', 'content' => 'Benar', 'is_correct' => true]);
        $optFalse = (new Option)->forceFill(['id' => 's1', 'content' => 'Salah', 'is_correct' => false]);
        $q->setRelation('options', collect([$optTrue, $optFalse]));

        $grader = new GradesBenarSalah;

        $res = $grader->grade($q, ['option_id' => 'b1']);
        $this->assertEquals(10.0, $res['earned_marks']);
        $this->assertTrue($res['is_correct']);
    }

    public function test_menjodohkan_proportional_pairs(): void
    {
        $q = (new Question)->forceFill(['marks' => 20]);
        $p1 = (new Option)->forceFill(['id' => 'p1', 'content' => 'Indonesia', 'pair_match' => 'Jakarta']);
        $p2 = (new Option)->forceFill(['id' => 'p2', 'content' => 'Jepang', 'pair_match' => 'Tokyo']);
        $q->setRelation('options', collect([$p1, $p2]));

        $grader = new GradesMenjodohkan;

        // Fully correct
        $res = $grader->grade($q, ['pairs' => ['p1' => 'Jakarta', 'p2' => 'Tokyo']]);
        $this->assertEquals(20.0, $res['earned_marks']);
        $this->assertTrue($res['is_correct']);

        // Half correct
        $resHalf = $grader->grade($q, ['pairs' => ['p1' => 'Jakarta', 'p2' => 'Kyoto']]);
        $this->assertEquals(10.0, $resHalf['earned_marks']);
        $this->assertFalse($resHalf['is_correct']);
    }

    public function test_isian_rumpang_normalization_and_numeric_tolerance(): void
    {
        $q = (new Question)->forceFill([
            'marks' => 20,
            'metadata' => [
                'blanks' => [
                    0 => ['accepted' => ['Yogyakarta', 'Jogja'], 'ignore_punct' => true],
                    1 => ['accepted' => ['2.5', '2,5'], 'ignore_punct' => true],
                ],
            ],
        ]);
        $q->setRelation('options', collect());

        $grader = new GradesIsianRumpang;

        // Test lowercase, extra whitespace, punctuation ignoring, and comma decimal
        $res = $grader->grade($q, [
            'blanks' => [
                0 => '   yogyakarta!  ',
                1 => '2,50', // numeric equality 2,50 == 2.5
            ],
        ]);

        $this->assertEquals(20.0, $res['earned_marks']);
        $this->assertTrue($res['is_correct']);

        // Only 1 blank correct
        $resHalf = $grader->grade($q, [
            'blanks' => [
                0 => 'Solo',
                1 => '2.5',
            ],
        ]);
        $this->assertEquals(10.0, $resHalf['earned_marks']);
    }

    public function test_esai_requires_manual_grading_unless_scored(): void
    {
        $q = (new Question)->forceFill(['id' => 'essay-1', 'marks' => 25]);
        $grader = new GradesEsai;

        // Pending manual grading
        $resPending = $grader->grade($q, ['text_answer' => 'Jawaban esai siswa']);
        $this->assertEquals(0.0, $resPending['earned_marks']);
        $this->assertTrue($resPending['needs_manual']);

        // Scored by teacher in context
        $resScored = $grader->grade($q, ['text_answer' => 'Jawaban esai siswa'], null, [
            'manual_essay_scores' => [
                'essay-1' => ['score' => 22, 'comment' => 'Penjelasan sangat baik'],
            ],
        ]);
        $this->assertEquals(22.0, $resScored['earned_marks']);
        $this->assertFalse($resScored['needs_manual']);
    }
}
