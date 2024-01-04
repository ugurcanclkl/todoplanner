<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Developer;

class DeveloperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $developers = [
            [
                'name'         => 'developer 1',
                'weekly'       => 45,
                'experience'   => 1,
            ],
            [
                'name'         => 'developer 2',
                'weekly'       => 45,
                'experience'   => 2,
            ],
            [
                'name'         => 'developer 3',
                'weekly'       => 45,
                'experience'   => 3,
            ],
            [
                'name'         => 'developer 4',
                'weekly'       => 45,
                'experience'   => 4,
            ],
            [
                'name'         => 'developer 5',
                'weekly'       => 45,
                'experience'   => 5,
            ],
        ];

        foreach ($developers as $developer) {
            Developer::create([
                'title'       => $developer['name'],
                'weekly_working_time' => $developer['weekly'],
                'experience' => $developer['experience'],
            ]);
        }
    }
}
