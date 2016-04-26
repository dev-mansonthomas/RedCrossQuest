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
      $faker = Faker\Factory::create("fr_FR");
      $faker->seed(20160426);

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
      /* ********************************************** */
      $data = [];
      for ($i = 0; $i < 100; $i++) {
        $data[] = [
          'ul_id'    => 2,
          'created'  => date('Y-m-d H:i:s')
        ];
      }
      $tronc = $this->table("tronc");
      //print_r($data);
      $tronc->insert($data)
        ->save();
      /* ********************************************** */
      $data = [];
      for ($i = 0; $i < 100; $i++) {
        $data[] = [
          'ul_id'    => 2,
          'created'  => date('Y-m-d H:i:s')
        ];
      }
      $tronc = $this->table("tronc");
      //print_r($data);
      $tronc->insert($data)
        ->save();
      /* ********************************************** */




      $data = [];
      for ($i = 0; $i < 450; $i++) {
        $data[] = [
          'queteur_id'        => $faker->biasedNumberBetween($min = 1, $max = 200, $function = 'sqrt'),
          'point_quete_id'    => $faker->biasedNumberBetween($min = 1, $max = 15 , $function = 'sqrt'),
          'tronc_id'          => $faker->biasedNumberBetween($min = 1, $max = 100 , $function = 'sqrt'),
          'depart_theorique'  => date('Y-m-d H:i:s'),
          'depart'            => date('Y-m-d H:i:s'),
          'retour'            => date('Y-m-d H:i:s'),
          'euro500'          => $faker->biasedNumberBetween($min = 0, $max = 0  , $function = 'sqrt'),
          'euro200'          => $faker->biasedNumberBetween($min = 0, $max = 0  , $function = 'sqrt'),
          'euro100'          => $faker->biasedNumberBetween($min = 0, $max = 1  , $function = 'sqrt'),
          'euro50'           => $faker->biasedNumberBetween($min = 0, $max = 1  , $function = 'sqrt'),
          'euro20'           => $faker->biasedNumberBetween($min = 0, $max = 2  , $function = 'sqrt'),
          'euro10'           => $faker->biasedNumberBetween($min = 0, $max = 3  , $function = 'sqrt'),
          'euro5'            => $faker->biasedNumberBetween($min = 0, $max = 5  , $function = 'sqrt'),
          'euro2'            => $faker->biasedNumberBetween($min = 1, $max = 40 , $function = 'sqrt'),
          'euro1'            => $faker->biasedNumberBetween($min = 1, $max = 50 , $function = 'sqrt'),
          'cents50'          => $faker->biasedNumberBetween($min = 1, $max = 60 , $function = 'sqrt'),
          'cents20'          => $faker->biasedNumberBetween($min = 1, $max = 70 , $function = 'sqrt'),
          'cents10'          => $faker->biasedNumberBetween($min = 1, $max = 70 , $function = 'sqrt'),
          'cents5'           => $faker->biasedNumberBetween($min = 1, $max = 80 , $function = 'sqrt'),
          'cents2'           => $faker->biasedNumberBetween($min = 1, $max = 60 , $function = 'sqrt'),
          'cent1'            => $faker->biasedNumberBetween($min = 1, $max = 50 , $function = 'sqrt'),
          'foreign_coins'    => $faker->biasedNumberBetween($min = 1, $max = 15 , $function = 'sqrt'),
          'foreign_banknote' => $faker->biasedNumberBetween($min = 1, $max = 15 , $function = 'sqrt')
        ];
      }

      $tronc_queteur = $this->table("tronc_queteur");
      //print_r($data);
      $tronc_queteur->insert( $data)
        ->save();


    }
}
