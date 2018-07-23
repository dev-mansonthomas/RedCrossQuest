<?php
namespace RedCrossQuest\Entity;



class YearlyGoalEntity  extends Entity
{
  public $id           ;
  public $ul_id        ;
  public $year         ;
  public $amount       ;
  public $day_1_percentage;
  public $day_2_percentage;
  public $day_3_percentage;
  public $day_4_percentage;
  public $day_5_percentage;
  public $day_6_percentage;
  public $day_7_percentage;
  public $day_8_percentage;
  public $day_9_percentage;

  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   */
  public function __construct(array $data)
  {
    $this->getInteger('id'               , $data);
    $this->getInteger('ul_id'            , $data);
    $this->getInteger('year'             , $data);
    $this->getInteger('amount'           , $data);

    $this->getInteger('day_1_percentage' , $data);
    $this->getInteger('day_2_percentage' , $data);
    $this->getInteger('day_3_percentage' , $data);
    $this->getInteger('day_4_percentage' , $data);
    $this->getInteger('day_5_percentage' , $data);
    $this->getInteger('day_6_percentage' , $data);
    $this->getInteger('day_7_percentage' , $data);
    $this->getInteger('day_8_percentage' , $data);
    $this->getInteger('day_9_percentage' , $data);

  }
}
