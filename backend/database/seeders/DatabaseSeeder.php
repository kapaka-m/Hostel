<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Dorm;
use App\Models\DormAdmin;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Student;
use App\Models\Ticket;
use App\Models\University;
use App\Models\User;
use App\Services\RoomAssignmentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SystemSettingsSeeder::class);

        $university = University::firstOrCreate(
            ['code' => 'DEFAULT'],
            [
                'name' => 'Default University',
                'address' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'is_active' => true,
            ]
        );

        $superAdmin = User::where('email', 'superadmin@test.com')->first();

        if (!$superAdmin) {
            $superAdmin = User::create([
                'name' => 'Super Admin',
                'email' => 'superadmin@test.com',
                'password' => Hash::make('Super@12345'),
                'role' => User::ROLE_SUPER_ADMIN,
                'university_id' => $university->id,
            ]);
        }

        $universityAdmin = User::where('email', 'uniadmin@test.com')->first();

        if (!$universityAdmin) {
            $universityAdmin = User::create([
                'name' => 'University Admin',
                'email' => 'uniadmin@test.com',
                'password' => Hash::make('Uni@12345'),
                'role' => User::ROLE_UNIVERSITY_ADMIN,
                'university_id' => $university->id,
            ]);
        }

        $dormNames = ['Dorm A', 'Dorm B', 'Dorm C', 'Dorm D'];
        $dorms = [];

        foreach ($dormNames as $name) {
            $dorms[$name] = Dorm::firstOrCreate(
                ['name' => $name, 'university_id' => $university->id],
                [
                    'address' => null,
                    'code' => strtoupper(str_replace(' ', '_', $name)),
                    'capacity' => 80,
                    'status' => 'ACTIVE',
                ]
            );
        }

        $dormA = $dorms['Dorm A'];

        $dormAdminUser = User::where('email', 'dormadmin@test.com')->first();

        if (!$dormAdminUser) {
            $dormAdminUser = User::create([
                'name' => 'Dorm Admin',
                'email' => 'dormadmin@test.com',
                'password' => Hash::make('Dorm@12345'),
                'role' => User::ROLE_DORM_ADMIN,
                'university_id' => $university->id,
            ]);
        }

        DormAdmin::firstOrCreate(
            ['user_id' => $dormAdminUser->id],
            ['dorm_id' => $dormA->id]
        );

        $floors = [];
        for ($i = 1; $i <= 4; $i++) {
            $floors[$i] = Floor::firstOrCreate(
                [
                    'dorm_id' => $dormA->id,
                    'number' => $i,
                ],
                [
                    'bathrooms' => 4,
                    'kitchens' => 2,
                    'showers' => 2,
                ]
            );
        }

        foreach ($floors as $floorNumber => $floor) {
            for ($roomIndex = 1; $roomIndex <= 20; $roomIndex++) {
                $roomNumber = (string) (($floorNumber * 100) + $roomIndex);

                Room::firstOrCreate(
                    [
                        'dorm_id' => $dormA->id,
                        'room_number' => $roomNumber,
                    ],
                    [
                        'floor_id' => $floor->id,
                        'capacity' => 4,
                        'status' => 'AVAILABLE',
                    ]
                );
            }
        }

        $studentUser = User::where('email', 'student1@test.com')->first();

        if (!$studentUser) {
            $studentUser = User::create([
                'name' => 'Student One',
                'email' => 'student1@test.com',
                'password' => Hash::make('Stud@12345'),
                'role' => User::ROLE_STUDENT,
                'university_id' => $university->id,
            ]);
        }

        $student = Student::firstOrCreate(
            [
                'user_id' => $studentUser->id,
            ],
            [
                'dorm_id' => $dormA->id,
                'full_name' => 'Student One',
                'student_no' => '2024001',
                'phone' => null,
            ]
        );

        $room101 = Room::where('dorm_id', $dormA->id)
            ->where('room_number', '101')
            ->first();

        if ($room101) {
            (new RoomAssignmentService)->assignStudentToRoom($student, $room101);
        }

        Ticket::firstOrCreate([
            'subject' => 'Air conditioner leak',
            'university_id' => $university->id,
            'dorm_id' => $dormA->id,
            'created_by' => $dormAdminUser->id,
        ], [
            'description' => 'AC leaking in room 101. Needs maintenance.',
            'category' => 'Maintenance',
            'priority' => 'HIGH',
            'status' => 'OPEN',
            'assigned_to' => $dormAdminUser->id,
        ]);

        Announcement::firstOrCreate([
            'title' => 'Quiet hours reminder',
            'university_id' => $university->id,
            'dorm_id' => $dormA->id,
        ], [
            'created_by' => $universityAdmin->id,
            'body' => 'Quiet hours are from 10 PM to 6 AM. Please be respectful.',
            'audience' => 'DORM',
            'status' => 'PUBLISHED',
            'publish_at' => now(),
        ]);

        $this->call(PermissionsSeeder::class);
    }
}
