<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            ['name' => 'الريال السعودي', 'code' => 'SAR', 'symbol' => '﷼', 'description' => 'الريال السعودي', 'is_default' => true],
        ];

        foreach ($currencies as $currency) {
            // Check if currency already exists
            if (Currency::where('code', $currency['code'])->exists()) {
                continue;
            }

            try {
                Currency::create($currency);
            } catch (\Exception $e) {
                $this->command->error('Failed to create currency: ' . $currency['code']);
                continue;
            }
        }

        $this->command->info('Created currencies successfully!');
    }
}
