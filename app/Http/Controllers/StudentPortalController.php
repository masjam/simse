<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Student\CbtController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\JadwalController;
use App\Http\Controllers\Student\KtsController;
use App\Http\Controllers\Student\LmsController;
use App\Http\Controllers\Student\MateriMingguanController;
use App\Http\Controllers\Student\NilaiController;
use App\Http\Controllers\Student\PresensiController;
use App\Http\Requests\Student\RecordExamViolationRequest;
use App\Http\Requests\Student\SaveExamAnswerRequest;
use App\Http\Requests\Student\SubmitAssignmentRequest;
use App\Models\Assignment;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentPortalController extends Controller
{
    public function dashboard(): Response
    {
        return app(DashboardController::class)->dashboard();
    }

    public function materiMingguan(Request $request): Response
    {
        return app(MateriMingguanController::class)->materiMingguan($request);
    }

    public function jadwal(): Response
    {
        return app(JadwalController::class)->index();
    }

    public function nilai(): Response
    {
        return app(NilaiController::class)->index();
    }

    public function presensi(): Response
    {
        return app(PresensiController::class)->index();
    }

    public function lms(Request $request): Response
    {
        return app(LmsController::class)->index($request);
    }

    public function downloadLessonAttachment(Lesson $lesson): StreamedResponse
    {
        return app(LmsController::class)->downloadLessonAttachment($lesson);
    }

    public function submitAssignment(SubmitAssignmentRequest $request, Assignment $assignment): RedirectResponse
    {
        return app(LmsController::class)->submitAssignment($request, $assignment);
    }

    public function downloadSubmissionFile(Submission $submission): StreamedResponse
    {
        return app(LmsController::class)->downloadSubmissionFile($submission);
    }

    public function cbt(): Response
    {
        return app(CbtController::class)->index();
    }

    public function startExam(Exam $exam): Response|RedirectResponse
    {
        return app(CbtController::class)->startExam($exam);
    }

    public function saveExamAnswer(SaveExamAnswerRequest $request, Exam $exam): JsonResponse
    {
        return app(CbtController::class)->saveExamAnswer($request, $exam);
    }

    public function recordExamViolation(RecordExamViolationRequest $request, Exam $exam): JsonResponse
    {
        return app(CbtController::class)->recordExamViolation($request, $exam);
    }

    public function submitExam(Request $request, Exam $exam): RedirectResponse
    {
        return app(CbtController::class)->submitExam($request, $exam);
    }

    public function kts(): Response
    {
        return app(KtsController::class)->index();
    }
}
