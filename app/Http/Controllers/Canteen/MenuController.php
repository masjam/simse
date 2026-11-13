<?php

namespace App\Http\Controllers\Canteen;

use App\Http\Requests\Canteen\StoreMenuRequest;
use App\Http\Requests\Canteen\UpdateMenuRequest;
use App\Models\CanteenMenu;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends BaseCanteenController
{
    public function index(): Response
    {
        $stand = $this->resolveStand();

        $menus = CanteenMenu::where('canteen_stand_id', $stand->id)
            ->orderBy('name')
            ->get()
            ->map(fn (CanteenMenu $menu): array => [
                'id' => $menu->id,
                'name' => $menu->name,
                'price' => (float) $menu->price,
                'stock' => $menu->stock,
                'description' => $menu->description,
                'photo' => $menu->photo,
                'is_available' => $menu->is_available,
            ]);

        return Inertia::render('Canteen/Menus', [
            'stand' => [
                'id' => $stand->id,
                'name' => $stand->name,
                'is_open' => $stand->is_open,
            ],
            'menus' => $menus,
        ]);
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $stand = $this->resolveStand();

        CanteenMenu::create([
            'canteen_stand_id' => $stand->id,
            ...$request->validated(),
        ]);

        return redirect()->back()->with('success', 'Menu baru berhasil ditambahkan ke stand.');
    }

    public function update(UpdateMenuRequest $request, CanteenMenu $menu): RedirectResponse
    {
        $stand = $this->resolveStand();
        if ($menu->canteen_stand_id !== $stand->id) {
            abort(403, 'Anda tidak berhak mengubah menu milik stand lain.');
        }

        $menu->update($request->validated());

        return redirect()->back()->with('success', 'Data menu berhasil diperbarui.');
    }

    public function destroy(CanteenMenu $menu): RedirectResponse
    {
        $stand = $this->resolveStand();
        if ($menu->canteen_stand_id !== $stand->id) {
            abort(403, 'Anda tidak berhak menghapus menu milik stand lain.');
        }

        $menu->delete();

        return redirect()->back()->with('success', 'Menu berhasil dihapus.');
    }
}
