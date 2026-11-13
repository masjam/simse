<?php

namespace App\Http\Controllers\Canteen;

use App\Http\Controllers\Controller;
use App\Models\CanteenStand;
use Illuminate\Support\Facades\Auth;

abstract class BaseCanteenController extends Controller
{
    protected function resolveStand(): CanteenStand
    {
        $user = Auth::user();

        return CanteenStand::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Stand '.$user->name,
                'description' => 'Stand makanan & minuman sehat sekolah',
                'balance' => 0,
                'is_open' => true,
            ]
        );
    }
}
