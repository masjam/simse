<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Bendahara\DocumentController as BendaharaDocumentController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Teacher\DocumentController as TeacherDocumentController;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DocumentController extends Controller
{
    /**
     * Unduh e-Raport resmi anak bagi Orang Tua.
     */
    public function raport(Request $request, Student $student): Response
    {
        $user = $request->user();

        // Verifikasi kepemilikan orang tua terhadap siswa
        if (! $user->hasRole('Super Admin') && $student->parent_user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses mengunduh raport siswa ini.');
        }

        return app(TeacherDocumentController::class)->raport($request, $student);
    }

    /**
     * Unduh bukti pembayaran / kuitansi kasir resmi anak bagi Orang Tua.
     */
    public function pembayaran(Request $request, Payment $payment): Response
    {
        $user = $request->user();
        $payment->loadMissing('bill.student');
        $student = $payment->bill?->student;

        // Verifikasi kepemilikan orang tua terhadap pembayaran
        if (! $user->hasRole('Super Admin') && $student?->parent_user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses mengunduh kuitansi pembayaran ini.');
        }

        return app(BendaharaDocumentController::class)->paymentReceipt($request, $payment);
    }
}
