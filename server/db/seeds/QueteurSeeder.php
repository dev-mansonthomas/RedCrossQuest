<?php

use Phinx\Seed\AbstractSeed;

class QueteurSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
      $faker = Faker\Factory::create();
      $data = [];
      for ($i = 0; $i < 200; $i++) {
        $data[] = [
          'first_name'    => $faker->firstName,
          'last_name'     => $faker->lastName,
          'secteur'       => $faker->randomDigit,
          'nivol'         => $faker->randomNumber($nbDigits = 6).$faker->randomLetter,
          'email'         => $faker->email,
          'mobile'        => "06".$faker->randomNumber($nbDigits = 8),
          'created'       => date('Y-m-d H:i:s'),
          'updated'       => date('Y-m-d H:i:s'),
          'notes'         => $faker->text($maxNbChars = 200),
          'ul_id'         => 2
        ];
      }

      $queteur = $this->table("queteur");
      //print_r($data);
      $queteur->insert( $data)
              ->save();

    }
}
