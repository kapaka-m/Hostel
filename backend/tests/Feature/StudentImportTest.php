<?php

namespace Tests\Feature;

use App\Models\Dorm;
use App\Models\DormAdmin;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StudentImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    private function createUniversityAndDorm(): array
    {
        $university = University::create([
            'name' => 'Import University',
            'code' => 'IMPORT-UNI',
            'is_active' => true,
        ]);

        $dorm = Dorm::create([
            'name' => 'Import Dorm',
            'address' => null,
            'university_id' => $university->id,
            'code' => 'IMPORT-DORM',
            'capacity' => 50,
            'status' => 'ACTIVE',
        ]);

        return [$university, $dorm];
    }

    private function makeCsvFile(Dorm $dorm): UploadedFile
    {
        $content = implode("\n", [
            'full_name,student_no,email,dorm_code',
            'Omar Hassan,102030,omar@test.com,' . $dorm->code,
        ]) . "\n";

        return UploadedFile::fake()->createWithContent('students.csv', $content);
    }

    public function test_university_admin_can_import_students(): void
    {
        [$university, $dorm] = $this->createUniversityAndDorm();

        $admin = User::factory()->create([
            'role' => User::ROLE_UNIVERSITY_ADMIN,
            'university_id' => $university->id,
        ]);

        $response = $this->postImportRequest($admin, $this->makeCsvFile($dorm));

        $response->assertStatus(200);
        $response->assertViewIs('admin.university.students.import-result');

        $this->assertDatabaseHas('users', [
            'email' => 'omar@test.com',
            'role' => User::ROLE_STUDENT,
        ]);
    }

    public function test_dorm_admin_cannot_import_students(): void
    {
        [$university, $dorm] = $this->createUniversityAndDorm();

        $admin = User::factory()->create([
            'role' => User::ROLE_DORM_ADMIN,
            'university_id' => $university->id,
        ]);

        DormAdmin::create([
            'user_id' => $admin->id,
            'dorm_id' => $dorm->id,
        ]);

        $response = $this->postImportRequest($admin, $this->makeCsvFile($dorm));

        $response->assertStatus(403);
    }

    public function test_student_cannot_import_students(): void
    {
        [$university, $dorm] = $this->createUniversityAndDorm();

        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'university_id' => $university->id,
        ]);

        $response = $this->postImportRequest($student, $this->makeCsvFile($dorm));

        $response->assertStatus(403);
    }

    private function postImportRequest(User $user, UploadedFile $file): TestResponse
    {
        return $this->actingAs($user)->post(route('admin.university.students.import.store'), [
            'file' => $file,
        ]);
    }
}
