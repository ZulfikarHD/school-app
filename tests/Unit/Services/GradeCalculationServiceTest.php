<?php

namespace Tests\Unit\Services;

use App\Models\AttitudeGrade;
use App\Models\Grade;
use App\Models\GradeWeightConfig;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\GradeCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Unit tests untuk GradeCalculationService
 * Testing calculation logic, predikat, ranking, dan edge cases
 */
class GradeCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GradeCalculationService $service;

    protected User $teacher;

    protected SchoolClass $class;

    protected Student $student;

    protected Subject $subject;

    protected string $tahunAjaran;

    protected string $semester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new GradeCalculationService;

        // Set tahun ajaran dan semester dinamis
        $this->tahunAjaran = now()->month >= 7
            ? now()->year.'/'.(now()->year + 1)
            : (now()->year - 1).'/'.now()->year;
        $this->semester = now()->month >= 7 ? '1' : '2';

        // Create teacher
        $this->teacher = User::factory()->create([
            'role' => 'TEACHER',
        ]);

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

        // Create default weight config
        GradeWeightConfig::create([
            'tahun_ajaran' => $this->tahunAjaran,
            'subject_id' => null,
            'uh_weight' => 30,
            'uts_weight' => 25,
            'uas_weight' => 30,
            'praktik_weight' => 15,
            'is_default' => true,
        ]);
    }

    // ============================================================
    // CALCULATE FINAL GRADE TESTS
    // ============================================================

    /**
     * Test calculate final grade dengan semua komponen lengkap
     */
    public function test_calculate_final_grade_with_all_components(): void
    {
        // Create UH grades (average: 80)
        Grade::factory()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UH',
            'score' => 80,
        ]);

        // Create UTS grade
        Grade::factory()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UTS',
            'score' => 75,
        ]);

        // Create UAS grade
        Grade::factory()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UAS',
            'score' => 85,
        ]);

        // Create Praktik grade
        Grade::factory()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'PRAKTIK',
            'score' => 90,
        ]);

        $result = $this->service->calculateFinalGrade(
            $this->student->id,
            $this->subject->id,
            $this->tahunAjaran,
            $this->semester
        );

        // Expected: (80*30 + 75*25 + 85*30 + 90*15) / 100 = 81.75
        $expected = (80 * 30 + 75 * 25 + 85 * 30 + 90 * 15) / 100;

        $this->assertEquals(round($expected, 2), $result['final_grade']);
        $this->assertArrayHasKey('predikat', $result);
        $this->assertArrayHasKey('breakdown', $result);
    }

    /**
     * Test calculate final grade dengan multiple UH (rata-rata)
     */
    public function test_calculate_final_grade_with_multiple_uh(): void
    {
        // Create multiple UH grades (70 + 80 + 90 = 240 / 3 = 80)
        Grade::factory()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UH',
            'assessment_number' => 1,
            'score' => 70,
        ]);

        Grade::factory()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UH',
            'assessment_number' => 2,
            'score' => 80,
        ]);

        Grade::factory()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UH',
            'assessment_number' => 3,
            'score' => 90,
        ]);

        $result = $this->service->calculateFinalGrade(
            $this->student->id,
            $this->subject->id,
            $this->tahunAjaran,
            $this->semester
        );

        // Verify UH average is 80
        $this->assertEquals(80, $result['breakdown']['uh']['average']);
        $this->assertEquals(3, $result['breakdown']['uh']['count']);
    }

    /**
     * Test calculate final grade dengan missing components (zeros)
     */
    public function test_calculate_final_grade_handles_missing_components(): void
    {
        // Only create UH, no UTS, UAS, Praktik
        Grade::factory()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UH',
            'score' => 80,
        ]);

        $result = $this->service->calculateFinalGrade(
            $this->student->id,
            $this->subject->id,
            $this->tahunAjaran,
            $this->semester
        );

        // Missing components should be treated as 0
        $this->assertEquals(0, $result['breakdown']['uts']['score']);
        $this->assertEquals(0, $result['breakdown']['uas']['score']);
        $this->assertEquals(0, $result['breakdown']['praktik']['average']);

        // Final grade: (80*30 + 0*25 + 0*30 + 0*15) / 100 = 24
        $expected = (80 * 30) / 100;
        $this->assertEquals($expected, $result['final_grade']);
    }

    /**
     * Test calculate final grade dengan zero score
     */
    public function test_calculate_final_grade_handles_zero_scores(): void
    {
        // Create all grades with score 0
        Grade::factory()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UH',
            'score' => 0,
        ]);

        Grade::factory()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UTS',
            'score' => 0,
        ]);

        $result = $this->service->calculateFinalGrade(
            $this->student->id,
            $this->subject->id,
            $this->tahunAjaran,
            $this->semester
        );

        $this->assertEquals(0, $result['final_grade']);
        $this->assertEquals('D', $result['predikat']);
    }

    // ============================================================
    // PREDIKAT TESTS
    // ============================================================

    /**
     * Test predikat A (90-100)
     */
    public function test_predikat_a_for_score_90_to_100(): void
    {
        $this->assertEquals('A', GradeCalculationService::getPredikat(90));
        $this->assertEquals('A', GradeCalculationService::getPredikat(95));
        $this->assertEquals('A', GradeCalculationService::getPredikat(100));
    }

    /**
     * Test predikat B (80-89.99)
     */
    public function test_predikat_b_for_score_80_to_89(): void
    {
        $this->assertEquals('B', GradeCalculationService::getPredikat(80));
        $this->assertEquals('B', GradeCalculationService::getPredikat(85));
        $this->assertEquals('B', GradeCalculationService::getPredikat(89.99));
    }

    /**
     * Test predikat C (70-79.99)
     */
    public function test_predikat_c_for_score_70_to_79(): void
    {
        $this->assertEquals('C', GradeCalculationService::getPredikat(70));
        $this->assertEquals('C', GradeCalculationService::getPredikat(75));
        $this->assertEquals('C', GradeCalculationService::getPredikat(79.99));
    }

    /**
     * Test predikat D (0-69.99)
     */
    public function test_predikat_d_for_score_below_70(): void
    {
        $this->assertEquals('D', GradeCalculationService::getPredikat(0));
        $this->assertEquals('D', GradeCalculationService::getPredikat(50));
        $this->assertEquals('D', GradeCalculationService::getPredikat(69.99));
    }

    /**
     * Test predikat label
     */
    public function test_get_predikat_label(): void
    {
        $this->assertEquals('Sangat Baik', GradeCalculationService::getPredikatLabel(95));
        $this->assertEquals('Baik', GradeCalculationService::getPredikatLabel(85));
        $this->assertEquals('Cukup', GradeCalculationService::getPredikatLabel(75));
        $this->assertEquals('Kurang', GradeCalculationService::getPredikatLabel(50));
    }

    // ============================================================
    // CALCULATE CLASS AVERAGE TESTS
    // ============================================================

    /**
     * Test calculate class average
     */
    public function test_calculate_class_average(): void
    {
        // Create another student
        $student2 = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);

        // Student 1: score 80
        $this->createCompleteGradesForStudent($this->student, 80);

        // Student 2: score 90
        $this->createCompleteGradesForStudent($student2, 90);

        $result = $this->service->calculateClassAverage(
            $this->class->id,
            $this->subject->id,
            $this->tahunAjaran,
            $this->semester
        );

        $this->assertEquals(2, $result['student_count']);
        $this->assertArrayHasKey('grade_distribution', $result);
    }

    /**
     * Test calculate class average dengan empty class
     */
    public function test_calculate_class_average_empty_class(): void
    {
        // Create empty class
        $emptyClass = SchoolClass::factory()->create([
            'tahun_ajaran' => $this->tahunAjaran,
            'is_active' => true,
        ]);

        $result = $this->service->calculateClassAverage(
            $emptyClass->id,
            $this->subject->id,
            $this->tahunAjaran,
            $this->semester
        );

        $this->assertEquals(0, $result['average']);
        $this->assertEquals(0, $result['student_count']);
        $this->assertEquals('D', $result['predikat']);
    }

    // ============================================================
    // RANKING TESTS
    // ============================================================

    /**
     * Test calculate ranking dengan multiple students
     */
    public function test_calculate_ranking(): void
    {
        // Subject already attached in setUp() via DB::table('teacher_subjects')

        // Create additional students
        $student2 = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);

        $student3 = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);

        // Student 1: 70 (should be rank 3)
        $this->createCompleteGradesForStudent($this->student, 70);

        // Student 2: 90 (should be rank 1)
        $this->createCompleteGradesForStudent($student2, 90);

        // Student 3: 80 (should be rank 2)
        $this->createCompleteGradesForStudent($student3, 80);

        $rankings = $this->service->calculateRanking(
            $this->class->id,
            $this->tahunAjaran,
            $this->semester
        );

        $this->assertCount(3, $rankings);

        // Verify ranking order (sorted by average desc)
        $this->assertEquals(1, $rankings[0]['rank']);
        $this->assertEquals($student2->id, $rankings[0]['student_id']);

        $this->assertEquals(2, $rankings[1]['rank']);
        $this->assertEquals($student3->id, $rankings[1]['student_id']);

        $this->assertEquals(3, $rankings[2]['rank']);
        $this->assertEquals($this->student->id, $rankings[2]['student_id']);
    }

    /**
     * Test calculate ranking dengan tied scores (same rank)
     */
    public function test_calculate_ranking_handles_tied_scores(): void
    {
        // Subject already attached in setUp() via DB::table('teacher_subjects')

        // Create additional student
        $student2 = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);

        // Both students have same score
        $this->createCompleteGradesForStudent($this->student, 85);
        $this->createCompleteGradesForStudent($student2, 85);

        $rankings = $this->service->calculateRanking(
            $this->class->id,
            $this->tahunAjaran,
            $this->semester
        );

        $this->assertCount(2, $rankings);

        // Both should have rank 1 (tied)
        $this->assertEquals(1, $rankings[0]['rank']);
        $this->assertEquals(1, $rankings[1]['rank']);
    }

    /**
     * Test calculate ranking dengan empty class
     */
    public function test_calculate_ranking_empty_class(): void
    {
        $emptyClass = SchoolClass::factory()->create([
            'tahun_ajaran' => $this->tahunAjaran,
            'is_active' => true,
        ]);

        $rankings = $this->service->calculateRanking(
            $emptyClass->id,
            $this->tahunAjaran,
            $this->semester
        );

        $this->assertTrue($rankings->isEmpty());
    }

    // ============================================================
    // STUDENT GRADE SUMMARY TESTS
    // ============================================================

    /**
     * Test get student grade summary
     */
    public function test_get_student_grade_summary(): void
    {
        // Subject already attached in setUp() via DB::table('teacher_subjects')

        $this->createCompleteGradesForStudent($this->student, 85);

        $summary = $this->service->getStudentGradeSummary(
            $this->student->id,
            $this->tahunAjaran,
            $this->semester
        );

        $this->assertArrayHasKey('subjects', $summary);
        $this->assertArrayHasKey('overall_average', $summary);
        $this->assertArrayHasKey('rank', $summary);
        $this->assertNotEmpty($summary['subjects']);
    }

    /**
     * Test get student grade summary untuk student tanpa kelas
     */
    public function test_get_student_grade_summary_without_class(): void
    {
        $studentWithoutClass = Student::factory()->create([
            'kelas_id' => null,
            'status' => 'aktif',
        ]);

        $summary = $this->service->getStudentGradeSummary(
            $studentWithoutClass->id,
            $this->tahunAjaran,
            $this->semester
        );

        $this->assertEmpty($summary['subjects']);
        $this->assertEquals(0, $summary['overall_average']);
        $this->assertNull($summary['rank']);
    }

    // ============================================================
    // WEIGHT CONFIG TESTS
    // ============================================================

    /**
     * Test calculation with custom weight config
     */
    public function test_calculate_with_custom_weight_config(): void
    {
        // Create subject-specific weight config
        GradeWeightConfig::create([
            'tahun_ajaran' => $this->tahunAjaran,
            'subject_id' => $this->subject->id,
            'uh_weight' => 40, // Higher weight for UH
            'uts_weight' => 20,
            'uas_weight' => 30,
            'praktik_weight' => 10,
            'is_default' => false,
        ]);

        $this->createCompleteGradesForStudent($this->student, 80);

        $result = $this->service->calculateFinalGrade(
            $this->student->id,
            $this->subject->id,
            $this->tahunAjaran,
            $this->semester
        );

        // Verify using subject-specific weights
        $this->assertEquals(40, $result['breakdown']['uh']['weight']);
        $this->assertEquals(20, $result['breakdown']['uts']['weight']);
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    /**
     * Helper: Create complete grades for student dengan score yang sama
     */
    protected function createCompleteGradesForStudent(Student $student, float $score): void
    {
        Grade::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UH',
            'score' => $score,
        ]);

        Grade::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UTS',
            'score' => $score,
        ]);

        Grade::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UAS',
            'score' => $score,
        ]);

        AttitudeGrade::factory()->create([
            'student_id' => $student->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);
    }
}
