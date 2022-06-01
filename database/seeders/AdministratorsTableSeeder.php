<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdministratorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'name' => '管理者',
            'email' => 'rese20220418@gmail.com',
            'password'  => Hash::make('rese20220418')
        ];
        DB::table('administrators')->insert($param);
    }
}
