<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //before running this seeder, make sure to off the gate key  in AuthServiceProvider.php

        //1
        Role::create([
            'name' => 'Admin'
        ]);

        //2
        Role::create([
            'name' => 'General'
        ]);
        
        //3
        Role::create([
            'name' => 'SuperVisor'
        ]);

        //4
        Role::create([
            'name' => 'Import'
        ]);

        //5
        Role::create([
            'name' => 'Export'
        ]);

        
 

        
    }
}
