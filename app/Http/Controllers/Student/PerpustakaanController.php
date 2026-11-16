<?php

namespace App\Http\Controllers\Student;

use App\Models\Book;
use App\Models\BookLoan;
use App\Services\Gamification\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PerpustakaanController extends BaseStudentController
{
    public function __construct(
        private readonly PointService $pointService,
    ) {}

    public function index(Request $request): Response
    {
        $student = $this->resolveStudent();
        $header = $this->formatStudentHeader($student);

        $category = (string) $request->query('category', '');
        $search = (string) $request->query('search', '');

        $booksQuery = Book::query()->latest();

        if ($category !== '') {
            $booksQuery->where('category', $category);
        }

        if ($search !== '') {
            $booksQuery->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('publisher', 'like', "%{$search}%");
            });
        }

        $books = $booksQuery->get()->map(fn (Book $b): array => [
            'id' => $b->id,
            'title' => $b->title,
            'author' => $b->author,
            'publisher' => $b->publisher,
            'isbn' => $b->isbn,
            'category' => $b->category,
            'cover_photo' => $b->cover_photo,
            'has_ebook' => ! empty($b->file_path),
            'read_url' => ! empty($b->file_path) ? route('siswa.perpustakaan.read', $b->id) : null,
            'physical_stock' => (int) $b->physical_stock,
            'is_available' => (int) $b->physical_stock > 0,
        ])->values();

        $categories = Book::pluck('category')->filter()->unique()->values()->all();

        // Riwayat Peminjaman Buku Siswa
        $loans = BookLoan::with('book')
            ->where('user_id', $student->user_id)
            ->latest('loan_date')
            ->get()
            ->map(fn (BookLoan $bl): array => [
                'id' => $bl->id,
                'book_title' => $bl->book?->title ?? 'Buku Perpustakaan',
                'category' => $bl->book?->category ?? 'Umum',
                'loan_date' => $bl->loan_date?->translatedFormat('d M Y') ?? '-',
                'due_date' => $bl->due_date?->translatedFormat('d M Y') ?? '-',
                'return_date' => $bl->return_date?->translatedFormat('d M Y'),
                'status' => $bl->status, // Dipinjam, Dikembalikan, Terlambat
                'is_overdue' => $bl->status === 'Dipinjam' && $bl->due_date && $bl->due_date->isPast(),
            ])
            ->values();

        $summary = [
            'borrowed_count' => $loans->where('status', 'Dipinjam')->count(),
            'returned_count' => $loans->where('status', 'Dikembalikan')->count(),
            'total_loans' => $loans->count(),
            'total_available_books' => Book::count(),
        ];

        return Inertia::render('Student/Perpustakaan', [
            'student' => $header,
            'books' => $books,
            'categories' => $categories,
            'selectedCategory' => $category,
            'searchQuery' => $search,
            'loans' => $loans,
            'summary' => $summary,
        ]);
    }

    public function read(Book $book): BinaryFileResponse
    {
        $student = $this->resolveStudent();

        if (empty($book->file_path) || ! Storage::disk('local')->exists($book->file_path)) {
            abort(404, 'Berkas e-book tidak ditemukan pada disk perpustakaan.');
        }

        // Apresiasi literasi siswa melalui gamifikasi poin
        $this->pointService->award(
            $student,
            'book_borrowed',
            $book,
            "Membaca e-book perpustakaan: {$book->title}"
        );

        $path = Storage::disk('local')->path($book->file_path);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.basename($book->file_path).'"',
        ]);
    }
}
