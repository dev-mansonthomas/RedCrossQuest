<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

require '../../vendor/autoload.php';


use RedCrossQuest\routes\routesActions\namedDonations\CreateNamedDonation;
use RedCrossQuest\routes\routesActions\namedDonations\GetNamedDonation;
use RedCrossQuest\routes\routesActions\namedDonations\ListNamedDonations;
use RedCrossQuest\routes\routesActions\namedDonations\UpdateNamedDonation;

/**
 * Fetch the named donation of an UL
 *
 * Dispo pour le role admin local
 */
$app->get(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/namedDonations', ListNamedDonations::class);

/**
 *
 * get one named donation
 *
 */
$app->get(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/namedDonations/{id}', GetNamedDonation::class);

/**
 *
 * Update named donation
 *
 */
$app->put(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/namedDonations/{id}', UpdateNamedDonation::class);


/**
 * Create named donation
 */
$app->post(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/namedDonations', CreateNamedDonation::class);
