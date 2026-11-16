<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSubjectSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);

        $this->admin = User::factory()->create(['name' => 'Admin Penguji']);
        $this->admin->assignRole('Super Admin');
    }

    public function test_admin_can_soft_delete_subject(): void
    {
        $subject = Subject::create([
            'code' => 'PAI-01',
            'name' => 'Pendidikan Agama Islam & Kemuhammadiyahan',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.subjects.destroy', $subject->id));

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Assert record is soft deleted (deleted_at is set, still in DB)
        $this->assertSoftDeleted('subjects', [
            'id' => $subject->id,
            'code' => 'PAI-01',
        ]);

        // Subject count in active scope is 0
        $this->assertCount(0, Subject::all());
        // Subject count in trashed scope is 1
        $this->assertCount(1, Subject::onlyTrashed()->get());
    }

    public function test_soft_deleted_subject_appears_in_trash_and_can_be_restored(): void
    {
        $subject = Subject::create([
            'code' => 'MTK-01',
            'name' => 'Matematika Al-Mujahidin',
        ]);
        $subject->delete();

        // 1. Appears in Trash index
        $response = $this->actingAs($this->admin)
            ->get(route('admin.trash.index', ['type' => 'subject']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Trash/Index')
            ->where('currentType', 'subject')
            ->has('items.data', 1)
        );

        // 2. Restore subject from Trash
        $restoreResponse = $this->actingAs($this->admin)
            ->post(route('admin.trash.restore', [
                'type' => 'subject',
                'id' => $subject->id,
            ]));

        $restoreResponse->assertStatus(302);
        $restoreResponse->assertSessionHas('success');

        // Subject should be restored (deleted_at is null)
        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'deleted_at' => null,
        ]);
        $this->assertCount(1, Subject::all());
    }

    public function test_non_admin_cannot_delete_subject(): void
    {
        $guru = User::factory()->create(['name' => 'Guru Pengajar']);
        $guru->assignRole('Guru Pengajar');

        $subject = Subject::create([
            'code' => 'B-IND-01',
            'name' => 'Bahasa Indonesia',
        ]);

        $response = $this->actingAs($guru)
            ->delete(route('admin.subjects.destroy', $subject->id));

        // Forbidden or redirected
        $this->assertTrue(in_array($response->status(), [403, 302]));
        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'deleted_at' => null,
        ]);
    }
}

