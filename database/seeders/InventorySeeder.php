<?php

namespace Database\Seeders;

use App\Models\Inventories;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'code' => 'BRG001',
                'name' => 'Laptop Asus ROG',
                'price' => 15000000,
                'stock' => 10,
            ],
            [
                'code' => 'BRG002',
                'name' => 'Mouse Logitech Wireless',
                'price' => 150000,
                'stock' => 50,
            ],
            [
                'code' => 'BRG003',
                'name' => 'Keyboard Mechanical Rexus',
                'price' => 450000,
                'stock' => 25,
            ],
            [
                'code' => 'BRG004',
                'name' => 'Monitor LG 24 Inch',
                'price' => 2000000,
                'stock' => 15,
            ],
            [
                'code' => 'BRG005',
                'name' => 'Printer Epson L3210',
                'price' => 2500000,
                'stock' => 5,
            ],
        ];

        foreach ($items as $item) {
            Inventories::create($item);
        }
    }
}
