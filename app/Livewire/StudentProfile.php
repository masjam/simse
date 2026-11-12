<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Livewire equivalent of the `print.student` Blade view.
 *
 * Renders a printable student profile (Profil Peserta Didik) containing
 * personal data, contact & address, parent/guardian data, achievements,
 * and periodical growth records.
 */
class StudentProfile extends Component
{
    public int $studentId;

    public function mount(int $studentId): void
    {
        $this->studentId = $studentId;
    }

    #[Computed]
    public function user(): ?User
    {
        return User::with([
            'student.achievements',
            'student.periodics',
            'student.studentClass.tingkat',
        ])->whereHas('roles', function ($query): void {
            $query->where('name', 'Siswa');
        })->find($this->studentId);
    }

    public function render(): View
    {
        return view('livewire.student-profile');
    }
}
