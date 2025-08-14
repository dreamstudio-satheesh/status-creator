<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FontSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fonts = [
            ['name' => 'Latha', 'family' => 'Latha', 'is_tamil' => true],
            ['name' => 'Catamaran', 'family' => 'Catamaran', 'is_tamil' => true],
            ['name' => 'Mukta Malar', 'family' => 'Mukta Malar', 'is_tamil' => true],
            ['name' => 'Hind Madurai', 'family' => 'Hind Madurai', 'is_tamil' => true],
            ['name' => 'Roboto', 'family' => 'Roboto', 'is_tamil' => false],
            ['name' => 'Open Sans', 'family' => 'Open Sans', 'is_tamil' => false],
            ['name' => 'Montserrat', 'family' => 'Montserrat', 'is_tamil' => false],
        ];

        foreach ($fonts as $font) {
            \App\Models\Font::create($font);
        }
    }
}
