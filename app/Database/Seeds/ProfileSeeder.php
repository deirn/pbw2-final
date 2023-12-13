<?php

namespace App\Database\Seeds;

use App\Entities\User;
use App\Models\ProfileModel;
use App\Models\UserModel;
use CodeIgniter\Database\Seeder;
use CodeIgniter\Files\File;
use Faker\Factory;

class ProfileSeeder extends Seeder
{
    public function run()
    {
        echo 'Seeding Profiles' . PHP_EOL;

        $faker = Factory::create();
        $faker->seed(1234);

        /** @var User[] */
        $users = model(UserModel::class)->findAll();

        foreach ($users as $user) {
            $avatarUrl = "https://i.pravatar.cc/400?u={$user->username}";
            echo "  Downloading avatar from {$avatarUrl}" . PHP_EOL;

            $tempPath = '../writable/uploads/avatar_seed';
            write_file($tempPath, file_get_contents($avatarUrl));

            $profile = $user->getProfile();
            $profile->uploadAvatar(new File($tempPath));
            $profile->bio = $faker->text(160);
            model(ProfileModel::class)->save($profile);
        }
    }
}
