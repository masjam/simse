<?php

namespace App\Http\Controllers;

use App\Http\Requests\Staff\BorrowBookRequest;
use App\Http\Requests\Staff\StaffAttendanceRequest;
use App\Models\Attendance;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Payroll;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    private function calculateDistanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function dashboard(): Response
    {
        $user = Auth::user();
        $today = today()->toDateString();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $monthlyAttendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->get();

        $activeLoans = BookLoan::where('status', 'Dipinjam')->count();
        $overdueLoans = BookLoan::where('status', 'Dipinjam')
            ->whereDate('due_date', '<', $today)
            ->count();

        $latestPayroll = Payroll::with('components')
            ->where(function ($q) use ($user): void {
                $q->where('user_id', $user->id);
            })
            ->latest()
            ->first();

        return Inertia::render('Staff/Dashboard', [
            'stats' => [
                'today_status' => $todayAttendance?->status ?? 'Belum Presensi',
                'time_in' => $todayAttendance?->time_in ? Carbon::parse($todayAttendance->time_in)->format('H:i') : '-',
                'time_out' => $todayAttendance?->time_out ? Carbon::parse($todayAttendance->time_out)->format('H:i') : '-',
                'present_days' => $monthlyAttendances->where('status', 'Hadir')->count(),
                'total_books' => Book::count(),
                'active_loans' => $activeLoans,
                'overdue_loans' => $overdueLoans,
                'latest_salary' => (float) ($latestPayroll?->total_salary ?? 0),
            ],
            'recentAttendances' => $monthlyAttendances->take(5)->map(fn ($a): array => [
                'id' => $a->id,
                'date' => Carbon::parse($a->date)->format('d M Y'),
                'time_in' => $a->time_in ? Carbon::parse($a->time_in)->format('H:i') : '-',
                'time_out' => $a->time_out ? Carbon::parse($a->time_out)->format('H:i') : '-',
                'status' => $a->status,
            ])->values(),
        ]);
    }

    public function absensi(): Response
    {
        $user = Auth::user();
        $today = today()->toDateString();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $history = Attendance::where('user_id', $user->id)
            ->orderByDesc('date')
            ->take(30)
            ->get()
            ->map(fn (Attendance $att): array => [
                'id' => $att->id,
                'date' => Carbon::parse($att->date)->format('d M Y'),
                'time_in' => $att->time_in ? Carbon::parse($att->time_in)->format('H:i') : '-',
                'time_out' => $att->time_out ? Carbon::parse($att->time_out)->format('H:i') : '-',
                'status' => $att->status,
                'location_lat' => $att->location_lat,
                'location_lng' => $att->location_lng,
            ]);

        $schoolLat = (float) (SystemSetting::getValue('school_latitude', '-7.9664'));
        $schoolLng = (float) (SystemSetting::getValue('school_longitude', '110.6035'));
        $allowedRadius = (int) (SystemSetting::getValue('school_radius_meters', '150'));

        return Inertia::render('Staff/Absensi', [
            'todayAttendance' => $todayAttendance ? [
                'id' => $todayAttendance->id,
                'status' => $todayAttendance->status,
                'time_in' => $todayAttendance->time_in ? Carbon::parse($todayAttendance->time_in)->format('H:i') : null,
                'time_out' => $todayAttendance->time_out ? Carbon::parse($todayAttendance->time_out)->format('H:i') : null,
            ] : null,
            'schoolGeo' => [
                'latitude' => $schoolLat,
                'longitude' => $schoolLng,
                'radius_meters' => $allowedRadius,
                'school_name' => SystemSetting::getValue('school_name', 'SD Muhammadiyah Al Mujahidin Wonosari'),
            ],
            'history' => $history,
        ]);
    }

    public function storeAbsensi(StaffAttendanceRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $schoolLat = (float) (SystemSetting::getValue('school_latitude', '-7.9664'));
        $schoolLng = (float) (SystemSetting::getValue('school_longitude', '110.6035'));
        $allowedRadius = (int) (SystemSetting::getValue('school_radius_meters', '150'));

        $userLat = (float) $validated['latitude'];
        $userLng = (float) $validated['longitude'];

        $distance = $this->calculateDistanceMeters($schoolLat, $schoolLng, $userLat, $userLng);

        if ($distance > $allowedRadius) {
            throw ValidationException::withMessages([
                'latitude' => 'Lokasi Anda berada di luar radius sekolah (terdeteksi '.round($distance).' meter, batas maksimal '.$allowedRadius.' meter).',
            ]);
        }

        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($validated['type'] === 'check_in') {
            if ($attendance && $attendance->time_in) {
                return redirect()->back()->with('error', 'Anda sudah melakukan presensi masuk hari ini.');
            }

            $status = $now->format('H:i') > '07:15' ? 'Terlambat' : 'Hadir';

            Attendance::updateOrCreate(
                ['user_id' => $user->id, 'date' => $today],
                [
                    'time_in' => $currentTime,
                    'status' => $status,
                    'location_lat' => (string) $userLat,
                    'location_lng' => (string) $userLng,
                    'photo_proof' => $validated['photo_proof'] ?? null,
                ]
            );

            return redirect()->back()->with('success', "Presensi masuk berhasil tercatat pukul {$now->format('H:i')} ({$status}).");
        }

        if (! $attendance || ! $attendance->time_in) {
            return redirect()->back()->with('error', 'Anda belum melakukan presensi masuk hari ini.');
        }

        if ($attendance->time_out) {
            return redirect()->back()->with('error', 'Anda sudah melakukan presensi pulang hari ini.');
        }

        $attendance->update([
            'time_out' => $currentTime,
            'location_lat' => (string) $userLat,
            'location_lng' => (string) $userLng,
        ]);

        return redirect()->back()->with('success', "Presensi pulang berhasil tercatat pukul {$now->format('H:i')}.");
    }

    public function perpustakaan(Request $request): Response
    {
        $search = (string) $request->query('search', '');
        $statusFilter = (string) $request->query('status', 'all');

        $books = Book::when($search, fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('author', 'like', "%{$search}%"))
            ->orderBy('title')
            ->get()
            ->map(fn (Book $b): array => [
                'id' => $b->id,
                'title' => $b->title,
                'author' => $b->author ?? '-',
                'isbn' => $b->isbn ?? '-',
                'category' => $b->category ?? 'Umum',
                'physical_stock' => $b->physical_stock,
            ]);

        $loansQuery = BookLoan::with(['book', 'user'])
            ->latest('loan_date');

        if ($statusFilter !== 'all') {
            $loansQuery->where('status', $statusFilter);
        }

        $loans = $loansQuery->take(40)->get()->map(fn (BookLoan $loan): array => [
            'id' => $loan->id,
            'book_title' => $loan->book?->title ?? 'Buku',
            'borrower_name' => $loan->user?->name ?? 'Peminjam',
            'borrower_email' => $loan->user?->email ?? '-',
            'loan_date' => Carbon::parse($loan->loan_date)->format('d M Y'),
            'due_date' => Carbon::parse($loan->due_date)->format('d M Y'),
            'return_date' => $loan->return_date ? Carbon::parse($loan->return_date)->format('d M Y') : null,
            'status' => $loan->status,
            'is_overdue' => $loan->status === 'Dipinjam' && Carbon::parse($loan->due_date)->isPast(),
            'notes' => $loan->notes,
        ]);

        $eligibleBorrowers = User::role(['Siswa', 'Guru Pengajar', 'Staf Sekolah'])
            ->orderBy('name')
            ->take(50)
            ->get(['id', 'name', 'email']);

        return Inertia::render('Staff/Perpustakaan', [
            'books' => $books,
            'loans' => $loans,
            'eligibleBorrowers' => $eligibleBorrowers,
            'filters' => [
                'search' => $search,
                'status' => $statusFilter,
            ],
            'stats' => [
                'total_titles' => Book::count(),
                'total_stock' => Book::sum('physical_stock'),
                'active_loans' => BookLoan::where('status', 'Dipinjam')->count(),
                'overdue_loans' => BookLoan::where('status', 'Dipinjam')->whereDate('due_date', '<', today())->count(),
            ],
        ]);
    }

    public function borrowBook(BorrowBookRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $book = Book::where('id', $validated['book_id'])->lockForUpdate()->firstOrFail();

            if ($book->physical_stock <= 0) {
                throw ValidationException::withMessages([
                    'book_id' => 'Stok fisik buku ini sedang habis.',
                ]);
            }

            $book->decrement('physical_stock', 1);

            $loanDate = today();
            $dueDate = today()->addDays((int) $validated['loan_duration_days']);

            BookLoan::create([
                'book_id' => $book->id,
                'user_id' => $validated['borrower_id'],
                'loan_date' => $loanDate,
                'due_date' => $dueDate,
                'status' => 'Dipinjam',
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->back()->with('success', 'Peminjaman buku berhasil dicatat.');
    }

    public function returnBook(BookLoan $bookLoan): RedirectResponse
    {
        if ($bookLoan->status === 'Dikembalikan') {
            return redirect()->back()->with('error', 'Buku ini sudah dikembalikan sebelumnya.');
        }

        DB::transaction(function () use ($bookLoan): void {
            $book = Book::where('id', $bookLoan->book_id)->lockForUpdate()->first();
            if ($book) {
                $book->increment('physical_stock', 1);
            }

            $bookLoan->update([
                'status' => 'Dikembalikan',
                'return_date' => today(),
            ]);
        });

        return redirect()->back()->with('success', 'Pengembalian buku berhasil diproses dan stok telah ditambahkan kembali.');
    }

    public function gaji(): Response
    {
        $user = Auth::user();

        $payrolls = Payroll::with('components')
            ->where(function ($q) use ($user): void {
                $q->where('user_id', $user->id);
            })
            ->latest('year')
            ->latest('created_at')
            ->get();

        $latestPayroll = $payrolls->first();

        $earnings = $latestPayroll
            ? $latestPayroll->components->where('type', 'Penerimaan')->map(fn ($c): array => ['label' => $c->name, 'amount' => (float) $c->amount])->values()
            : [];
        $deductions = $latestPayroll
            ? $latestPayroll->components->where('type', 'Potongan')->map(fn ($c): array => ['label' => $c->name, 'amount' => (float) $c->amount])->values()
            : [];

        $slipGaji = $latestPayroll ? [
            'id' => $latestPayroll->id,
            'month' => $latestPayroll->month.' '.$latestPayroll->year,
            'name' => $user->name,
            'nip' => 'STAF-'.strtoupper(substr(str_replace('-', '', $user->id), 0, 8)),
            'jabatan' => 'Tenaga Kependidikan & Administrasi',
            'status' => strtolower($latestPayroll->status) === 'dibayar' ? 'paid' : 'pending',
            'paidDate' => '25 '.$latestPayroll->month.' '.$latestPayroll->year,
            'earnings' => $earnings,
            'deductions' => $deductions,
            'totalSalary' => (float) $latestPayroll->total_salary,
        ] : null;

        $payrollHistory = $payrolls->map(fn ($p): array => [
            'id' => $p->id,
            'month' => $p->month.' '.$p->year,
            'takeHome' => (float) $p->total_salary,
            'status' => strtolower($p->status) === 'dibayar' ? 'paid' : 'pending',
            'date' => '25 '.$p->month.' '.$p->year,
        ]);

        return Inertia::render('Staff/SlipGaji', [
            'slipGaji' => $slipGaji,
            'payrollHistory' => $payrollHistory,
        ]);
    }
}
