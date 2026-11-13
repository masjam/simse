<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Bendahara\DashboardController;
use App\Http\Controllers\Bendahara\DompetController;
use App\Http\Controllers\Bendahara\KantinReportController;
use App\Http\Controllers\Bendahara\KasirController;
use App\Http\Controllers\Bendahara\PayrollController;
use App\Http\Controllers\Bendahara\SppController;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\PayrollComponent;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Thin delegator — semua logika bisnis ada di controller domain Bendahara/*.
 * File ini hanya meneruskan panggilan agar nama route tidak berubah.
 */
class BendaharaController extends Controller
{
    // ── Dashboard ──────────────────────────────────────────────────────────
    public function dashboard(): Response
    {
        return app(DashboardController::class)->index();
    }

    // ── Kasir ──────────────────────────────────────────────────────────────
    public function kasir(Request $request): Response
    {
        return app(KasirController::class)->index($request);
    }

    public function storePayment(Request $request): RedirectResponse
    {
        return app(KasirController::class)->storePayment($request);
    }

    public function verifyPayment(Request $request, Payment $payment): RedirectResponse
    {
        return app(KasirController::class)->verifyPayment($request, $payment);
    }

    public function downloadReceipt(Payment $payment): StreamedResponse
    {
        return app(KasirController::class)->downloadReceipt($payment);
    }

    // ── SPP ────────────────────────────────────────────────────────────────
    public function spp(): Response
    {
        return app(SppController::class)->index();
    }

    public function storeBillType(Request $request): RedirectResponse
    {
        return app(SppController::class)->storeBillType($request);
    }

    public function updateBillType(Request $request, BillType $billType): RedirectResponse
    {
        return app(SppController::class)->updateBillType($request, $billType);
    }

    public function destroyBillType(BillType $billType): RedirectResponse
    {
        return app(SppController::class)->destroyBillType($billType);
    }

    public function generateBills(Request $request): RedirectResponse
    {
        return app(SppController::class)->generateBills($request);
    }

    public function destroyBill(Bill $bill): RedirectResponse
    {
        return app(SppController::class)->destroyBill($bill);
    }

    public function sendArrearsReminder(Request $request): RedirectResponse
    {
        return app(SppController::class)->sendArrearsReminder($request);
    }

    // ── Dompet ─────────────────────────────────────────────────────────────
    public function dompet(): Response
    {
        return app(DompetController::class)->index();
    }

    public function storeWalletTransaction(Request $request): RedirectResponse
    {
        return app(DompetController::class)->storeTransaction($request);
    }

    public function updateWalletPin(Request $request, Student $student): RedirectResponse
    {
        return app(DompetController::class)->updatePin($request, $student);
    }

    public function updateStudentRfid(Request $request, Student $student): RedirectResponse
    {
        return app(DompetController::class)->updateRfid($request, $student);
    }

    // ── Payroll ────────────────────────────────────────────────────────────
    public function payroll(Request $request): Response
    {
        return app(PayrollController::class)->index($request);
    }

    public function generatePayroll(Request $request): RedirectResponse
    {
        return app(PayrollController::class)->generate($request);
    }

    public function storePayrollComponent(Request $request, Payroll $payroll): RedirectResponse
    {
        return app(PayrollController::class)->storeComponent($request, $payroll);
    }

    public function destroyPayrollComponent(PayrollComponent $component): RedirectResponse
    {
        return app(PayrollController::class)->destroyComponent($component);
    }

    public function updatePayrollStatus(Request $request, Payroll $payroll): RedirectResponse
    {
        return app(PayrollController::class)->updateStatus($request, $payroll);
    }

    public function submitPayrollApproval(Request $request): RedirectResponse
    {
        return app(PayrollController::class)->submitApproval($request);
    }

    // ── Kantin Report ──────────────────────────────────────────────────────
    public function kantinReport(Request $request): Response
    {
        return app(KantinReportController::class)->index($request);
    }
}
