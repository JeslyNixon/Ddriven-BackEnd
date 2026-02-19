<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['code' => 'DRAFT', 'name' => 'Draft'],
            ['code' => 'COMPLETED', 'name' => 'Completed'],
            ['code' => 'APPROVED', 'name' => 'Approved'],
            ['code' => 'REJECTED', 'name' => 'Rejected'],
            ['code' => 'PENDING', 'name' => 'Pending Review'],
        ];

        foreach ($statuses as $status) {
            DB::table('status')->insert([
                'code' => $status['code'],
                'name' => $status['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
