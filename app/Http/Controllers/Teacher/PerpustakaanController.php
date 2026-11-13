<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Book;
use Inertia\Inertia;
use Inertia\Response;

class PerpustakaanController extends BaseTeacherController
{
    public function index(): Response
    {
        $books = Book::all()->map(function ($b) {
            return [
                'id' => $b->id,
                'title' => $b->title,
                'author' => $b->author ?? 'Penulis',
                'publisher' => $b->publisher ?? 'Penerbit',
                'isbn' => $b->isbn ?? '-',
                'category' => $b->category ?? 'Umum',
                'year' => 2026,
                'stock' => $b->physical_stock,
                'available' => max(0, $b->physical_stock - 2),
                'hasDigital' => ! empty($b->file_path),
                'cover' => $b->cover_photo ?? '📘',
            ];
        });

        $categories = Book::pluck('category')->filter()->unique()->prepend('Semua')->values();

        return Inertia::render('Teacher/Perpustakaan', [
            'books' => $books,
            'categories' => $categories,
        ]);
    }
}
