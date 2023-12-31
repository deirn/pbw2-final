<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AllSeeder extends Seeder
{
    public function run()
    {
        $this->call('UserSeeder');
        $this->call('ProfileSeeder');
        $this->call('ConnectionSeeder');
        $this->call('StatusSeeder');
        $this->call('EngagementSeeder');
    }
}
