<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IdeaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // -------------------------
        // Insert Ideas
        // -------------------------
        $ideas = [
            [
                'user_id' => 2,
                'title' => 'AI Attendance System',
                'description' => 'Build face recognition attendance system',
                'status' => 'pending',
                'image' => null,
                'links' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'title' => 'AI Attendance System',
                'description' => 'Build face recognition attendance system',
                'status' => 'pending',
                'image' => null,
                'links' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'title' => 'AI Attendance System',
                'description' => 'Build face recognition attendance system',
                'status' => 'pending',
                'image' => null,
                'links' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'title' => 'AI Attendance System',
                'description' => 'Build face recognition attendance system',
                'status' => 'pending',
                'image' => null,
                'links' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'title' => 'AI Attendance System',
                'description' => 'Build face recognition attendance system',
                'status' => 'in progress',
                'image' => null,
                'links' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'title' => 'AI Attendance System',
                'description' => 'Build face recognition attendance system',
                'status' => 'in progress',
                'image' => null,
                'links' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'title' => 'AI Attendance System',
                'description' => 'Build face recognition attendance system',
                'status' => 'in progress',
                'image' => null,
                'links' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'title' => 'AI Attendance System',
                'description' => 'Build face recognition attendance system',
                'status' => 'in progress',
                'image' => null,
                'links' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'title' => 'AI Attendance System',
                'description' => 'Build face recognition attendance system',
                'status' => 'completed',
                'image' => null,
                'links' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'title' => 'AI Attendance System',
                'description' => 'Build face recognition attendance system',
                'status' => 'completed',
                'image' => null,
                'links' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'title' => 'AI Attendance System',
                'description' => 'Build face recognition attendance system',
                'status' => 'completed',
                'image' => null,
                'links' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'title' => 'AI Attendance System',
                'description' => 'Build face recognition attendance system',
                'status' => 'completed',
                'image' => null,
                'links' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'title' => 'AI Attendance System',
                'description' => 'Build face recognition attendance system',
                'status' => 'completed',
                'image' => null,
                'links' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'title' => 'AI Attendance System',
                'description' => 'Build face recognition attendance system',
                'status' => 'completed',
                'image' => null,
                'links' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'title' => 'Laravel Booking App',
                'description' => 'Online catering booking platform',
                'status' => 'completed',
                'image' => null,
                'links' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('ideas')->insert($ideas);

        // -------------------------
        // Get inserted idea IDs
        // -------------------------
        $ideaIds = DB::table('ideas')->pluck('id');

        // -------------------------
        // Insert Steps
        // -------------------------
        $steps = [];

        foreach ($ideaIds as $ideaId) {
            $steps[] = [
                'idea_id' => $ideaId,
                'description' => 'Research requirements',
                'completed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $steps[] = [
                'idea_id' => $ideaId,
                'description' => 'Design database',
                'completed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $steps[] = [
                'idea_id' => $ideaId,
                'description' => 'Implement core features',
                'completed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('steps')->insert($steps);
    }
}
