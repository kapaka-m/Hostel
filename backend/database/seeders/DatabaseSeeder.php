<?php

namespace Database\Seeders;

use App\Models\Dorm;
use App\Models\DormAdmin;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Student;
use App\Models\User;
use App\Services\RoomAssignmentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SystemSettingsSeeder::class);

        $universityAdmin = User::create([
            'name' => 'University Admin',
            'email' => 'uniadmin@test.com',
            'password' => Hash::make('Uni@12345'),
            'role' => User::ROLE_UNIVERSITY_ADMIN,
        ]);

        $dormNames = ['Dorm A', 'Dorm B', 'Dorm C', 'Dorm D'];
        $dorms = [];

        foreach ($dormNames as $name) {
            $dorms[$name] = Dorm::create([
                'name' => $name,
                'address' => null,
            ]);
        }

        $dormA = $dorms['Dorm A'];

        $dormAdminUser = User::create([
            'name' => 'Dorm Admin',
            'email' => 'dormadmin@test.com',
            'password' => Hash::make('Dorm@12345'),
            'role' => User::ROLE_DORM_ADMIN,
        ]);

        DormAdmin::create([
            'user_id' => $dormAdminUser->id,
            'dorm_id' => $dormA->id,
        ]);

        $floors = [];
        for ($i = 1; $i <= 4; $i++) {
            $floors[$i] = Floor::create([
                'dorm_id' => $dormA->id,
                'number' => $i,
                'bathrooms' => 4,
                'kitchens' => 2,
                'showers' => 2,
            ]);
        }

        foreach ($floors as $floorNumber => $floor) {
            for ($roomIndex = 1; $roomIndex <= 20; $roomIndex++) {
                $roomNumber = (string) (($floorNumber * 100) + $roomIndex);

                Room::create([
                    'dorm_id' => $dormA->id,
                    'floor_id' => $floor->id,
                    'room_number' => $roomNumber,
                    'capacity' => 4,
                    'status' => 'AVAILABLE',
                ]);
            }
        }

        $studentUser = User::create([
            'name' => 'Student One',
            'email' => 'student1@test.com',
            'password' => Hash::make('Stud@12345'),
            'role' => User::ROLE_STUDENT,
        ]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'dorm_id' => $dormA->id,
            'full_name' => 'Student One',
            'student_no' => '2024001',
            'phone' => null,
        ]);

        $room101 = Room::where('dorm_id', $dormA->id)
            ->where('room_number', '101')
            ->first();

        if ($room101) {
            (new RoomAssignmentService())->assignStudentToRoom($student, $room101);
        }

        $this->call(PermissionsSeeder::class);
    }
}
