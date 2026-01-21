<?php

namespace Tests\Feature\Admin;

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature tests untuk Admin GradeController
 * Testing index, summary, export, dan filters
 */
class GradeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teacher;

    protected SchoolClass $class;

    protected Student $student;

    protected Subject $subject;

    protected string $tahunAjaran;

    protected string $semester;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        // Create teacher user
        $this->teacher = User::factory()->create([
            'role' => 'TEACHER',
        ]);

        // Set tahun ajaran dan semester dinamis
        $this->tahunAjaran = now()->month >= 7
            ? now()->year.'/'.(now()->year + 1)
            : (now()->year - 1).'/'.now()->year;
        $this->semester = now()->month >= 7 ? '1' : '2';

        // Create class
        $this->class = SchoolClass::factory()->create([
            'wali_kelas_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'is_active' => true,
        ]);

        // Create student
        $this->student = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);

        // Create subject
        $this->subject = Subject::factory()->create([
            'is_active' => true,
        ]);

        // Attach subject to class
        DB::table('teacher_subjects')->insert([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // ============================================================
    // INDEX TESTS
    // ============================================================

    /**
     * Test admin dapat mengakses halaman index grades
     */
    public function test_admin_can_access_grades_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/grades');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Grades/Index')
            ->has('grades')
            ->has('filters')
            ->has('classes')
            ->has('subjects')
            ->has('assessmentTypes')
            ->has('statistics')
        );
    }

    /**
     * Test admin dapat melihat grades dari semua teacher
     */
    public function test_admin_can_see_all_teachers_grades(): void
    {
        // Create grade dari teacher
        Grade::factory()->ulanganHarian()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        // Create another teacher and grade
        $teacher2 = User::factory()->create(['role' => 'TEACHER']);
        Grade::factory()->uts()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $teacher2->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/grades');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('grades.data', fn ($grades) => count($grades) >= 2)
        );
    }

    /**
     * Test non-admin tidak bisa akses grades index
     */
    public function test_non_admin_cannot_access_grades_index(): void
    {
        $parent = User::factory()->create(['role' => 'PARENT']);

        $response = $this->actingAs($parent)->get('/admin/grades');

        $response->assertForbidden();
    }

    /**
     * Test teacher tidak bisa akses admin grades
     */
    public function test_teacher_cannot_access_admin_grades_index(): void
    {
        $response = $this->actingAs($this->teacher)->get('/admin/grades');

        $response->assertForbidden();
    }

    // ============================================================
    // FILTER TESTS
    // ============================================================

    /**
     * Test admin dapat filter grades by semester
     */
    public function test_admin_can_filter_grades_by_semester(): void
    {
        // Create grade for semester 1
        Grade::factory()->ulanganHarian()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => '1',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/grades?semester=1');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('filters.semester', '1')
        );
    }

    /**
     * Test admin dapat filter grades by class
     */
    public function test_admin_can_filter_grades_by_class(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/grades?class_id={$this->class->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('filters.class_id', (string) $this->class->id)
        );
    }

    /**
     * Test admin dapat filter grades by subject
     */
    public function test_admin_can_filter_grades_by_subject(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/grades?subject_id={$this->subject->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('filters.subject_id', (string) $this->subject->id)
        );
    }

    /**
     * Test admin dapat filter grades by tahun ajaran
     */
    public function test_admin_can_filter_grades_by_tahun_ajaran(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/grades?tahun_ajaran={$this->tahunAjaran}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('filters.tahun_ajaran', $this->tahunAjaran)
        );
    }

    // ============================================================
    // SUMMARY TESTS
    // ============================================================

    /**
     * Test admin dapat mengakses halaman summary
     */
    public function test_admin_can_access_grades_summary(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/grades/summary');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Grades/Summary')
            ->has('filters')
            ->has('classes')
            ->has('availableTahunAjaran')
        );
    }

    /**
     * Test admin dapat melihat summary untuk kelas tertentu
     */
    public function test_admin_can_view_summary_for_class(): void
    {
        // Create grades for the class
        Grade::factory()->ulanganHarian()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/grades/summary?class_id={$this->class->id}&tahun_ajaran={$this->tahunAjaran}&semester={$this->semester}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Grades/Summary')
            ->has('summary')
        );
    }

    /**
     * Test summary tanpa class_id menampilkan null summary
     */
    public function test_summary_without_class_shows_null(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/grades/summary');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('summary', null)
        );
    }

    // ============================================================
    // EXPORT TESTS
    // ============================================================

    /**
     * Test admin dapat export grades ke CSV
     */
    public function test_admin_can_export_grades_csv(): void
    {
        // Create grades for the class
        Grade::factory()->ulanganHarian()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'score' => 85,
        ]);

        Grade::factory()->uts()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'score' => 80,
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/grades/export?class_id={$this->class->id}&tahun_ajaran={$this->tahunAjaran}&semester={$this->semester}");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    /**
     * Test export tanpa class_id returns error
     */
    public function test_export_without_class_id_returns_error(): void
    {
        $response = $this->actingAs($this->admin)
            ->get("/admin/grades/export?tahun_ajaran={$this->tahunAjaran}&semester={$this->semester}");

        $response->assertStatus(400);
    }

    // ============================================================
    // STATISTICS TESTS
    // ============================================================

    /**
     * Test statistics menampilkan data yang benar
     */
    public function test_index_shows_correct_statistics(): void
    {
        // Create multiple grades
        Grade::factory()->ulanganHarian()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'score' => 85,
        ]);

        Grade::factory()->uts()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'score' => 90,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/grades');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('statistics')
            ->where('statistics.total_grades', fn ($total) => $total > 0)
        );
    }

    // ============================================================
    // AUTHORIZATION TESTS
    // ============================================================

    /**
     * Test multiple admin users can access grades
     */
    public function test_multiple_admins_can_access_grades(): void
    {
        $admin2 = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin2)->get('/admin/grades');

        $response->assertStatus(200);
    }
}
