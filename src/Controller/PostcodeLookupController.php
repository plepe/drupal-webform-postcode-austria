<?php

namespace Drupal\webform_postcode_austria\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webform_postcode_austria\PostcodeLookup;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PostcodeLookupController.
 */
class PostcodeLookupController extends ControllerBase {

  /**
   * The postcode lookup service.
   *
   * @var \Drupal\webform_postcode_austria\PostcodeLookup
   *   The postcode lookup service.
   */
  protected $postcodeLookup;

  /**
   * PostcodeLookupController constructor.
   *
   * @param \Drupal\webform_postcode_austria\PostcodeLookup $postcode_lookup
   *   The postcode lookup service.
   */
  public function __construct(PostcodeLookup $postcode_lookup) {
    $this->postcodeLookup = $postcode_lookup;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('webform_postcode_austria.postcode_lookup')
    );
  }


  /**
   * Retrieves an entry of the postcode file based on the postcode.
   *
   * @param string $postcode
   *   The requested postcode
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Details about the postcode
   */
  public function getPostcode(string $postcode) {
    return new JsonResponse($this->postcodeLookup->getPostcode($postcode));
  }
}
