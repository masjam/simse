<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tingkat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TingkatController extends Controller
{
    public function index()
    {
        $tingkats = Tingkat::latest()->paginate(10);

        return Inertia::render('Admin/Tingkat/Index', [
            'tingkats' => $tingkats,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tingkats,name',
        ]);

        Tingkat::create($request->all());

        return back()->with('success', 'Tingkat berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $tingkat = Tingkat::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:tingkats,name,'.$id,
        ]);

        $tingkat->update($request->all());

        return back()->with('success', 'Tingkat berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        Tingkat::findOrFail($id)->delete();

        return back()->with('success', 'Tingkat berhasil dihapus.');
    }
}
