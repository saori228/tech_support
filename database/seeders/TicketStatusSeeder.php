<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TicketStatus::create(['name' => 'В обработке']);
        TicketStatus::create(['name' => 'Завершено']);
        TicketStatus::create(['name' => 'Приостановлено']);
    }
}
