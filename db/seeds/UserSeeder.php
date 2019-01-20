<?php

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    public function run()
    {
        $faker = Faker\Factory::create();
        $data = [];
        for ($i = 1001; $i < 2000; $i++) {
            $data[] = [
                'userid'    => "test$i",
                'name'      => "テスト$i",
                'password'  => "password",
                'email'     => "example-$i@example.com",
            ];
        }

        $this->table('users')->insert($data)->save();
    }
}
