<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cms\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CmsController extends Controller
{
    public function index(Request $request): Response
    {
        $category = (string) $request->query('category', 'all');
        $search = (string) $request->query('search', '');

        $query = Post::query()
            ->when($search, function ($q) use ($search): void {
                $q->where(function ($sub) use ($search): void {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            });

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        $posts = $query->latest()->paginate(10)->through(fn (Post $p): array => [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'category' => $p->category ?? 'Berita',
            'content' => $p->content,
            'image' => $p->image,
            'is_published' => (bool) $p->is_published,
            'created_at' => $p->created_at?->format('d M Y, H:i') ?? '-',
            'updated_at' => $p->updated_at?->format('d M Y, H:i') ?? '-',
        ]);

        return Inertia::render('System/Cms', [
            'posts' => $posts,
            'filters' => [
                'category' => $category,
                'search' => $search,
            ],
            'categories' => ['Berita', 'Pengumuman', 'Prestasi', 'Artikel'],
            'stats' => [
                'total' => Post::count(),
                'published' => Post::where('is_published', true)->count(),
                'draft' => Post::where('is_published', false)->count(),
            ],
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $slug = Post::generateSlug($validated['title']);

        Post::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'category' => $validated['category'],
            'content' => $validated['content'],
            'image' => $validated['image'] ?? null,
            'is_published' => $validated['is_published'],
        ]);

        return redirect()->route('sistem.website')->with('success', 'Artikel website berhasil disimpan.');
    }

    public function update(StorePostRequest $request, Post $post): RedirectResponse
    {
        $validated = $request->validated();

        if ($post->title !== $validated['title']) {
            $post->slug = Post::generateSlug($validated['title']);
        }

        $post->update([
            'title' => $validated['title'],
            'slug' => $post->slug,
            'category' => $validated['category'],
            'content' => $validated['content'],
            'image' => $validated['image'] ?? null,
            'is_published' => $validated['is_published'],
        ]);

        return redirect()->route('sistem.website')->with('success', 'Artikel website berhasil diperbarui.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('sistem.website')->with('success', 'Artikel website berhasil dihapus.');
    }

    public function togglePublish(Post $post): RedirectResponse
    {
        $post->update([
            'is_published' => ! $post->is_published,
        ]);

        $status = $post->is_published ? 'dipublikasikan' : 'dijadikan draf';

        return redirect()->back()->with('success', "Artikel '{$post->title}' berhasil {$status}.");
    }
}
