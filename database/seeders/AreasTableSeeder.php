<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AreasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $areas = ['東京都', '大阪府', '福岡県'];
        foreach ($areas as $area) {
            $param = [
                'name' => $area,
            ];
            DB::table('areas')->insert($param);
        }
    }
}
