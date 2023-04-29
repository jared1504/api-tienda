<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('products')->insert([
            'name'=>'coca 600ml',
            'precio_sale'=>16,
            'precio_order'=>14,
            'stock'=>10
        ]);
        DB::table('products')->insert([
            'name'=>'jugo 500ml',
            'precio_sale'=>16,
            'precio_order'=>14,
            'stock'=>10
        ]);
        DB::table('products')->insert([
            'name'=>'galletas oreo',
            'precio_sale'=>16,
            'precio_order'=>14,
            'stock'=>10
        ]);
    }
}
