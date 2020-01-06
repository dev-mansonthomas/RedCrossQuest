<?php
namespace RedCrossQuest\routes\routesActions\namedDonations;



class CreateNamedDonationResponse
{
  /**
   * @var int $namedDonationId the id of the newly created namedDonation
   */
  public $namedDonationId;

  protected $_fieldList = ["namedDonationId"];

  public function __construct(int $namedDonationId)
  {
    $this->namedDonationId       = $namedDonationId;

  }
}
