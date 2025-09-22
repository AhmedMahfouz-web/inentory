<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use App\Models\UserBranch;
use Illuminate\Database\Seeder;

class UserBranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users and branches
        $users = User::all();
        $branches = Branch::all();

        if ($users->isEmpty() || $branches->isEmpty()) {
            $this->command->info('No users or branches found. Please create users and branches first.');
            return;
        }

        // Sample assignments
        $assignments = [
            // First user gets access to first 2 branches with request permissions
            [
                'user_id' => $users->first()->id,
                'branch_id' => $branches->first()->id,
                'can_request' => true,
                'can_manage' => false,
            ],
            [
                'user_id' => $users->first()->id,
                'branch_id' => $branches->skip(1)->first()->id ?? $branches->first()->id,
                'can_request' => true,
                'can_manage' => false,
            ],
        ];

        // If we have more users, assign them to different branches
        if ($users->count() > 1) {
            $assignments[] = [
                'user_id' => $users->skip(1)->first()->id,
                'branch_id' => $branches->first()->id,
                'can_request' => true,
                'can_manage' => true, // This user can manage
            ];
        }

        // Create the assignments
        foreach ($assignments as $assignment) {
            UserBranch::updateOrCreate(
                [
                    'user_id' => $assignment['user_id'],
                    'branch_id' => $assignment['branch_id'],
                ],
                [
                    'can_request' => $assignment['can_request'],
                    'can_manage' => $assignment['can_manage'],
                ]
            );
        }

        $this->command->info('User-Branch assignments created successfully!');
        $this->command->info('Created ' . count($assignments) . ' assignments.');
    }
}
