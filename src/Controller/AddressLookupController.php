<?php

namespace Drupal\webform_postcode_austria\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webform_postcode_austria\AddressLookup;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AddressLookupController.
 */
class AddressLookupController extends ControllerBase {

  /**
   * The address lookup service.
   *
   * @var \Drupal\webform_postcode_austria\AddressLookup
   */
  protected $addressLookup;

  /**
   * AddressLookupController constructor.
   *
   * @param \Drupal\webform_postcode_austria\AddressLookup $address_lookup
   *   The address lookup service.
   */
  public function __construct(AddressLookup $address_lookup) {
    $this->addressLookup = $address_lookup;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('webform_postcode_austria.address_lookup')
    );
  }

  /**
   * Retrieves an address based on zipcode and house number.
   *
   * @param string $zipcode
   *   The zipcode.
   * @param string $houseNumber
   *   The house number.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The address as a JSON response.
   */
  public function getAddress(string $zipcode, string $houseNumber) {
    return new JsonResponse($this->addressLookup->getAddress($zipcode, $houseNumber));
  }

}
