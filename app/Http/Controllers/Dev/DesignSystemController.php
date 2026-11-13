<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DesignSystemController extends Controller
{
    public function __invoke(): Response
    {
        abort_if(app()->isProduction(), 404);

        return Inertia::render('Dev/DesignSystem', [
            'sampleStudents' => [
                ['id' => '1', 'name' => 'Ahmad Dahlan', 'nisn' => '0012345671', 'class' => '6A'],
                ['id' => '2', 'name' => 'Siti Walidah', 'nisn' => '0012345672', 'class' => '5B'],
                ['id' => '3', 'name' => 'Fakhruddin', 'nisn' => '0012345673', 'class' => '4A'],
            ],
            'sampleClasses' => [
                ['id' => 'c1', 'name' => 'Kelas 1A'],
                ['id' => 'c2', 'name' => 'Kelas 2A'],
                ['id' => 'c3', 'name' => 'Kelas 6A'],
            ],
            'sampleSubjects' => [
                ['id' => 's1', 'name' => 'Matematika'],
                ['id' => 's2', 'name' => 'Pendidikan Agama Islam'],
                ['id' => 's3', 'name' => 'Bahasa Arab'],
            ],
        ]);
    }
}
