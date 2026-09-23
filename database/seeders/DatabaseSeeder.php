<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin / Buyer ──
        $admin = User::firstOrCreate(
            ['email' => 'admin@consupman.com'],
            [
                'name'            => 'Blue Admin',
                'password'        => Hash::make('password'),
                'role'            => 'admin',
                'contact_number'  => '09171234567',
                'company_name'    => 'ABC Construction Corp',
                'company_address' => '123 Rizal St, Zamboanga City',
                'company_email'   => 'admin@abcconstruct.com',
                'company_tel'     => '(062) 123-4567',
            ]
        );

        // ── Supplier User ──
        $supplierUser = User::firstOrCreate(
            ['email' => 'supplier@cementpro.com'],
            [
                'name'            => 'CementPro Admin',
                'password'        => Hash::make('password'),
                'role'            => 'supplier',
                'company_name'    => 'CementPro Supply Co.',
                'company_address' => '456 Industrial Road',
                'company_city'    => 'Zamboanga City',
                'company_zip'     => '7000',
                'company_email'   => 'info@cementpro.com',
                'company_tel'     => '(062) 987-6543',
            ]
        );

        // ── Supplier record linked to supplier user ──
        $supplier = Supplier::firstOrCreate(
            ['email' => 'info@cementpro.com'],
            [
                'name'     => 'CementPro Supply',
                'phone'    => '09181234567',
                'address'  => '456 Industrial Road, Zamboanga City',
                'category' => 'cement',
                'user_id'  => $supplierUser->id,
            ]
        );

        // ── Sample Products for the supplier ──
        $sampleProducts = [
            [
                'name'        => 'Portland Cement (Type I)',
                'category'    => 'cement',
                'unit'        => 'bags',
                'price'       => 250.00,
                'description' => 'Standard Portland Cement, 40kg per bag. Ideal for general construction.',
                'is_available' => true,
            ],
            [
                'name'        => 'Waterproof Cement',
                'category'    => 'cement',
                'unit'        => 'bags',
                'price'       => 300.00,
                'description' => 'Water-resistant cement blend for foundations and wet areas.',
                'is_available' => true,
            ],
            [
                'name'        => 'Ready Mix Concrete',
                'category'    => 'cement',
                'unit'        => 'cubic meters',
                'price'       => 4500.00,
                'description' => 'Pre-mixed concrete delivered ready to pour.',
                'is_available' => true,
            ],
        ];

        foreach ($sampleProducts as $product) {
            SupplierProduct::firstOrCreate(
                [
                    'user_id' => $supplierUser->id,
                    'name'    => $product['name'],
                ],
                array_merge($product, ['user_id' => $supplierUser->id])
            );
        }

        // ── Second supplier (steel) ──
        $steelUser = User::firstOrCreate(
            ['email' => 'supplier@steelworks.com'],
            [
                'name'            => 'SteelWorks Manager',
                'password'        => Hash::make('password'),
                'role'            => 'supplier',
                'company_name'    => 'SteelWorks Inc.',
                'company_address' => '789 Metal Avenue',
                'company_city'    => 'Zamboanga City',
                'company_zip'     => '7000',
                'company_email'   => 'info@steelworks.com',
                'company_tel'     => '(062) 555-1234',
            ]
        );

        $steelSupplier = Supplier::firstOrCreate(
            ['email' => 'info@steelworks.com'],
            [
                'name'     => 'SteelWorks Inc.',
                'phone'    => '09189876543',
                'address'  => '789 Metal Avenue, Zamboanga City',
                'category' => 'steel',
                'user_id'  => $steelUser->id,
            ]
        );

        $steelProducts = [
            ['name' => '10mm Deformed Bar', 'category' => 'steel', 'unit' => 'pcs', 'price' => 285.00, 'description' => '10mm x 6m deformed steel bar (rebar).'],
            ['name' => '12mm Deformed Bar', 'category' => 'steel', 'unit' => 'pcs', 'price' => 390.00, 'description' => '12mm x 6m deformed steel bar (rebar).'],
            ['name' => 'Steel Pipe (2 inch)', 'category' => 'steel', 'unit' => 'pcs', 'price' => 650.00, 'description' => '2-inch diameter steel pipe, 6m length.'],
        ];

        foreach ($steelProducts as $product) {
            SupplierProduct::firstOrCreate(
                ['user_id' => $steelUser->id, 'name' => $product['name']],
                array_merge($product, ['user_id' => $steelUser->id, 'is_available' => true])
            );
        }
    }
}