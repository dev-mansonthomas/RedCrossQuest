<?php
namespace RedCrossQuest\Entity;



class TroncInUseEntity  extends Entity
{
  public $id;
  public $depart_theorique;
  public $depart;
  public $queteur_id;
  public $tronc_id;
  public $first_name;
  public $last_name;
  public $email;
  public $mobile;
  public $nivol;

  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @throws \Exception if a parse Date or JSON fails
   */
  public function __construct(array $data)
  {
    $this->getString  ('id'               , $data);
    $this->getDate    ('depart_theorique' , $data);
    $this->getDate    ('depart'           , $data);
    $this->getInteger ('queteur_id'       , $data);
    $this->getInteger ('tronc_id'         , $data);
    $this->getString  ('first_name'       , $data);
    $this->getString  ('last_name'        , $data);
    $this->getString  ('email'            , $data);
    $this->getString  ('mobile'           , $data);
    $this->getString  ('nivol'            , $data);
  }
}
