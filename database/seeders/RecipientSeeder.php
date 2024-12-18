<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Recipient;
use Illuminate\Support\Facades\DB;

class RecipientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recipients = [
            [
                'name' => 'Nelson Munyua',
                'phone_number' => '+254748442693',
                'wa_id' => '254748442693',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Nancy Munyua',
                'phone_number' => '+254742368897',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
              'name' => 'Serah Munyua',
                'phone_number' => '+254723770979',
                'created_at' => now(),
                'updated_at' => now(),
            ]

            ];

            // Disable foreign key checks if needed
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear the existing records
        Recipient::truncate();

        // Insert the new records
        foreach ($recipients as $recipient) {
            Recipient::create($recipient);
        }

         // Re-enable foreign key checks
         DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
