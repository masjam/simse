<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\Book;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\Question;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrashController extends Controller
{
    protected array $models = [
        'student' => Student::class,
        'lesson' => Lesson::class,
        'assignment' => Assignment::class,
        'exam' => Exam::class,
        'question' => Question::class,
        'book' => Book::class,
        'bill' => Bill::class,
        'post' => Post::class,
        'bill_type' => BillType::class,
    ];

    /**
     * Display soft-deleted items across models.
     */
    public function index(Request $request): Response
    {
        $currentType = $request->query('type', 'student');
        if (! array_key_exists($currentType, $this->models)) {
            $currentType = 'student';
        }

        $modelClass = $this->models[$currentType];

        $items = $modelClass::onlyTrashed()
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString()
            ->through(function ($item) use ($currentType) {
                $name = match ($currentType) {
                    'student' => $item->user?->name ?? "Siswa #{$item->id}",
                    'lesson', 'assignment', 'exam', 'book', 'post', 'bill_type' => $item->title ?? $item->name ?? "Item #{$item->id}",
                    'question' => strip_tags(substr($item->content ?? '', 0, 50)) ?: "Soal #{$item->id}",
                    'bill' => ($item->billType?->name ?? 'Tagihan').' - Rp '.number_format($item->amount_due ?? 0, 0, ',', '.'),
                    default => "Item #{$item->id}",
                };

                return [
                    'id' => $item->id,
                    'name' => $name,
                    'deleted_at' => $item->deleted_at?->translatedFormat('d M Y H:i:s'),
                ];
            });

        // Get trash counts for all tabs
        $counts = [];
        foreach ($this->models as $key => $class) {
            $counts[$key] = $class::onlyTrashed()->count();
        }

        return Inertia::render('Admin/Trash/Index', [
            'currentType' => $currentType,
            'items' => $items,
            'counts' => $counts,
        ]);
    }

    /**
     * Restore a soft-deleted item.
     */
    public function restore(string $type, string $id): RedirectResponse
    {
        if (! array_key_exists($type, $this->models)) {
            abort(404);
        }

        $modelClass = $this->models[$type];
        $item = $modelClass::onlyTrashed()->findOrFail($id);
        $item->restore();

        return redirect()->back()->with('success', 'Data berhasil dipulihkan dari tempat sampah.');
    }

    /**
     * Permanently delete an item.
     */
    public function forceDelete(string $type, string $id): RedirectResponse
    {
        if (! array_key_exists($type, $this->models)) {
            abort(404);
        }

        $modelClass = $this->models[$type];
        $item = $modelClass::onlyTrashed()->findOrFail($id);
        $item->forceDelete();

        return redirect()->back()->with('success', 'Data berhasil dihapus permanen.');
    }
}
