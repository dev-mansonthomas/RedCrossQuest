<?php
namespace RedCrossQuest\routes\routesActions\namedDonations;



class CreateNamedDonationResponse
{
  /**
   * @var ?int $namedDonationId the id of the newly created namedDonation
   */
  public ?int $namedDonationId;

  protected array $_fieldList = ["namedDonationId"];

  public function __construct(int $namedDonationId)
  {
    $this->namedDonationId       = $namedDonationId;

  }
}
