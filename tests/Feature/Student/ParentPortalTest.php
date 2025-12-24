<?php

namespace Tests\Feature\Student;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentPortalTest extends TestCase
{
    use RefreshDatabase;

    protected User $parent;

    protected Guardian $guardian;

    protected Student $child;

    protected function setUp(): void
    {
        parent::setUp();

        // Create parent user dengan guardian dan child
        $this->parent = User::factory()->create([
            'role' => 'PARENT',
            'status' => 'ACTIVE',
        ]);

        $this->guardian = Guardian::factory()->create([
            'user_id' => $this->parent->id,
        ]);

        $this->child = Student::factory()->create([
            'status' => 'aktif',
        ]);

        $this->child->guardians()->attach($this->guardian->id, [
            'is_primary_contact' => true,
        ]);
    }

    /**
     * Test parent dapat view list children
     */
    public function test_parent_can_view_their_children_list(): void
    {
        $response = $this->actingAs($this->parent)
            ->get(route('parent.children.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Parent/Children/Index')
            ->has('children', 1)
            ->where('children.0.id', $this->child->id)
        );
    }

    /**
     * Test parent dengan multiple children dapat view semua
     */
    public function test_parent_can_view_multiple_children(): void
    {
        // Create second child
        $child2 = Student::factory()->create(['status' => 'aktif']);
        $child2->guardians()->attach($this->guardian->id);

        $response = $this->actingAs($this->parent)
            ->get(route('parent.children.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('children', 2)
        );
    }

    /**
     * Test parent hanya melihat children yang aktif
     */
    public function test_parent_only_sees_active_children(): void
    {
        // Create inactive child
        $inactiveChild = Student::factory()->create(['status' => 'lulus']);
        $inactiveChild->guardians()->attach($this->guardian->id);

        $response = $this->actingAs($this->parent)
            ->get(route('parent.children.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('children', 1) // Only active child
            ->where('children.0.status', 'aktif')
        );
    }

    /**
     * Test parent dapat view detail child
     */
    public function test_parent_can_view_own_child_detail(): void
    {
        $response = $this->actingAs($this->parent)
            ->get(route('parent.children.show', $this->child));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Parent/Children/Show')
            ->has('student')
            ->where('student.id', $this->child->id)
        );
    }

    /**
     * Test parent tidak bisa view child orang lain
     */
    public function test_parent_cannot_view_other_parents_child(): void
    {
        $otherChild = Student::factory()->create();

        $response = $this->actingAs($this->parent)
            ->get(route('parent.children.show', $otherChild));

        $response->assertStatus(403);
    }

    /**
     * Test parent tanpa guardian record mendapat message
     */
    public function test_parent_without_guardian_record_gets_message(): void
    {
        $parentWithoutGuardian = User::factory()->create([
            'role' => 'PARENT',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($parentWithoutGuardian)
            ->get(route('parent.children.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('children', 0)
            ->has('message')
        );
    }

    /**
     * Test non-parent tidak bisa akses parent portal
     */
    public function test_non_parent_cannot_access_parent_portal(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)
            ->get(route('parent.children.index'));

        $response->assertStatus(403);
    }

    /**
     * Test parent view child detail includes guardians
     */
    public function test_parent_child_detail_includes_guardians(): void
    {
        $response = $this->actingAs($this->parent)
            ->get(route('parent.children.show', $this->child));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('student.guardians')
        );
    }

    /**
     * Test parent view child detail includes class history
     */
    public function test_parent_child_detail_includes_class_history(): void
    {
        // Create class history
        $this->child->classHistory()->create([
            'kelas_id' => 1,
            'tahun_ajaran' => '2024/2025',
            'wali_kelas' => 'Pak Budi',
        ]);

        $response = $this->actingAs($this->parent)
            ->get(route('parent.children.show', $this->child));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('student.class_history', 1)
        );
    }

    /**
     * Test unauthenticated user tidak bisa akses parent portal
     */
    public function test_unauthenticated_user_cannot_access_parent_portal(): void
    {
        $response = $this->get(route('parent.children.index'));

        $response->assertRedirect(route('login'));
    }
}
