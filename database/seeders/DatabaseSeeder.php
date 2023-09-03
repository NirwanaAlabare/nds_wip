<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory(10)->create();

        for ($i=0; $i < 10; $i++) {
            \App\Models\TemporaryPanel::create([
                'nama_panel' => Str::random(5),
            ]);
        }

        for ($i=0; $i < 100; $i++) {
            \App\Models\TemporaryBomItem::create([
                'so_det_id' => rand(60,76500),
                'panel_id' => rand(1,10),
            ]);
        }
    }
}
