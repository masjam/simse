<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class KantinController extends Controller
{
    public function index()
    {
        $walletBalance = 150000; // Mock saldo dompet digital siswa

        $stands = [
            [
                'id' => 1,
                'name' => 'Stand Ibu Siti (Kantin Sehat)',
                'is_open' => true,
                'menus' => [
                    ['id' => 101, 'name' => 'Nasi Goreng Spesial', 'price' => 15000, 'stock' => 20, 'photo' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80'],
                    ['id' => 102, 'name' => 'Ayam Geprek Sambal Matah', 'price' => 18000, 'stock' => 15, 'photo' => 'https://images.unsplash.com/photo-1562565652-a9e87054f1d4?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80'],
                    ['id' => 103, 'name' => 'Es Teh Manis', 'price' => 5000, 'stock' => 50, 'photo' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80'],
                ]
            ],
            [
                'id' => 2,
                'name' => 'Kantin Jujur (Koperasi)',
                'is_open' => true,
                'menus' => [
                    ['id' => 201, 'name' => 'Roti Bakar Coklat Keju', 'price' => 12000, 'stock' => 10, 'photo' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80'],
                    ['id' => 202, 'name' => 'Jus Mangga Segar', 'price' => 10000, 'stock' => 30, 'photo' => 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80'],
                ]
            ],
            [
                'id' => 3,
                'name' => 'Mang Oleh (Somay & Batagor)',
                'is_open' => false,
                'menus' => []
            ]
        ];

        return Inertia::render('Kantin/Index', [
            'stands' => $stands,
            'walletBalance' => $walletBalance
        ]);
    }
}
