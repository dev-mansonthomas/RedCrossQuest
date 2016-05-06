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

  public $point_quete_id;
  public $point_quete_name;
  public $depart_theorique;
  public $depart;
  public $retour;

  /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     */
    public function __construct($data)
    {
      if(array_key_exists('id', $data))
      {
        $this->id                       = $data['id'];
      }
      $this->email                    = $data['email'];
      $this->first_name               = $data['first_name'];
      $this->last_name                = $data['last_name'];
      $this->minor                    = $data['minor'];
      $this->secteur                  = $data['secteur'];
      $this->nivol                    = $data['nivol'];
      $this->mobile                   = $data['mobile'];
      if(array_key_exists('created', $data))
      {
      $this->created                  = $data['created'];
      }
      if(array_key_exists('updated', $data))
      {
      $this->updated                  = $data['updated'];
      }
      if(array_key_exists('parent_authorization', $data))
      {
        $this->parentAuthorization    = $data['parent_authorization'];
      }
      if(array_key_exists('temporary_volunteer_form', $data))
      {
      $this->temporaryVolunteerForm = $data['temporary_volunteer_form'];
      }
      if(array_key_exists('notes', $data))
      {
        $this->notes = $data['notes'];
      }
      $this->ul_id                    = $data['ul_id'];


      if(array_key_exists('point_quete_id', $data))
      {
        $this->point_quete_id = $data['point_quete_id'];;
      }
      if(array_key_exists('point_quete_name', $data))
      {
        $this->point_quete_name = $data['point_quete_name'];;
      }
      if(array_key_exists('depart_theorique', $data))
      {
        $this->depart_theorique = $data['depart_theorique'];;
      }
      if(array_key_exists('depart', $data))
      {
        $this->depart = $data['depart'];;
      }
      if(array_key_exists('retour', $data))
      {
        $this->retour = $data['retour'];;
      }




    }
}
