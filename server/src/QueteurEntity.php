<?php
namespace RedCrossQuest;

class QueteurEntity
{
  public $id;
  public $email                       ;
  public $first_name                  ;
  public $last_name                   ;
  public $minor                       ;
  public $secteur                     ;
  public $nivol                       ;
  public $mobile                      ;
  public $created                     ;
  public $updated                     ;
  public $parent_authorization        ;
  public $temporary_volunteer_form    ;
  public $notes                       ;
  public $ul_id                       ;

    /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     */
    public function __construct($data)
    {
      $this->id                       = $data['id'];
      $this->email                    = $data['email'];
      $this->first_name               = $data['first_name'];
      $this->last_name                = $data['last_name'];
      $this->minor                    = $data['minor'];
      $this->secteur                  = $data['secteur'];
      $this->nivol                    = $data['nivol'];
      $this->mobile                   = $data['mobile'];
      $this->created                  = $data['created'];
      $this->updated                  = $data['updated'];
      //$this->parentAuthorization    = $data['parent_authorization'];
      //$this->temporaryVolunteerForm = $data['temporary_volunteer_form'];
      $this->notes                    = $data['notes'];
      $this->ul_id                    = $data['ul_id'];
    }
}
