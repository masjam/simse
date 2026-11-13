<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Teacher\AbsensiController;
use App\Http\Controllers\Teacher\BkController;
use App\Http\Controllers\Teacher\CbtController;
use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\JadwalController;
use App\Http\Controllers\Teacher\JurnalController;
use App\Http\Controllers\Teacher\LmsController;
use App\Http\Controllers\Teacher\NilaiController;
use App\Http\Controllers\Teacher\PayrollController;
use App\Http\Controllers\Teacher\PerpustakaanController;
use App\Http\Requests\Teacher\StoreTeachingJournalRequest;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\Submission;
use App\Models\TeachingJournal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherController extends Controller
{
    public function dashboard(): Response
    {
        return app(DashboardController::class)->index();
    }

    public function jadwal(): Response
    {
        return app(JadwalController::class)->index();
    }

    public function absensi(Request $request): Response
    {
        return app(AbsensiController::class)->index($request);
    }

    public function storeAbsensi(Request $request): RedirectResponse
    {
        return app(AbsensiController::class)->store($request);
    }

    public function nilai(Request $request): Response
    {
        return app(NilaiController::class)->index($request);
    }

    public function storeNilai(Request $request): mixed
    {
        return app(NilaiController::class)->store($request);
    }

    public function exportNilai(Request $request): mixed
    {
        return app(NilaiController::class)->export($request);
    }

    public function importNilai(Request $request): RedirectResponse
    {
        return app(NilaiController::class)->import($request);
    }

    public function raport(Request $request): Response
    {
        return app(NilaiController::class)->raport($request);
    }

    public function lms(Request $request): Response
    {
        return app(LmsController::class)->index($request);
    }

    public function storeLesson(Request $request): RedirectResponse
    {
        return app(LmsController::class)->store($request);
    }

    public function downloadLessonAttachment(Lesson $lesson): StreamedResponse
    {
        return app(LmsController::class)->downloadAttachment($lesson);
    }

    public function destroyLesson(Lesson $lesson): RedirectResponse
    {
        return app(LmsController::class)->destroy($lesson);
    }

    public function tugas(Request $request): Response
    {
        return app(LmsController::class)->indexTugas($request);
    }

    public function storeAssignment(Request $request): RedirectResponse
    {
        return app(LmsController::class)->storeAssignment($request);
    }

    public function gradeSubmission(Request $request, Submission $submission): RedirectResponse
    {
        return app(LmsController::class)->gradeSubmission($request, $submission);
    }

    public function cbt(Request $request): Response
    {
        return app(CbtController::class)->index($request);
    }

    public function storeExam(Request $request): RedirectResponse
    {
        return app(CbtController::class)->storeExam($request);
    }

    public function storeQuestion(Request $request, Exam $exam): RedirectResponse
    {
        return app(CbtController::class)->storeQuestion($request, $exam);
    }

    public function toggleExam(Exam $exam): RedirectResponse
    {
        return app(CbtController::class)->toggleExam($exam);
    }

    public function bk(Request $request): Response
    {
        return app(BkController::class)->index($request);
    }

    public function storeViolation(Request $request): RedirectResponse
    {
        return app(BkController::class)->storeViolation($request);
    }

    public function storeCounseling(Request $request): RedirectResponse
    {
        return app(BkController::class)->storeCounseling($request);
    }

    public function perpustakaan(): Response
    {
        return app(PerpustakaanController::class)->index();
    }

    public function payroll(): Response
    {
        return app(PayrollController::class)->index();
    }

    public function jurnal(Request $request): Response
    {
        return app(JurnalController::class)->index($request);
    }

    public function storeJurnal(StoreTeachingJournalRequest $request): RedirectResponse
    {
        return app(JurnalController::class)->store($request);
    }

    public function updateJurnal(StoreTeachingJournalRequest $request, TeachingJournal $journal): RedirectResponse
    {
        return app(JurnalController::class)->update($request, $journal);
    }

    public function destroyJurnal(TeachingJournal $journal): RedirectResponse
    {
        return app(JurnalController::class)->destroy($journal);
    }
}
