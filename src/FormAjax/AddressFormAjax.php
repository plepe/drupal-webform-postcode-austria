<?php

namespace Drupal\webform_postcodeapi\FormAjax;

use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Drupal\webform_postcodeapi\Classes\FormValidation;

/**
 * Lookup street and city upon entering postal code and house number.
 */
class AddressFormAjax {

  /**
   * Ajax callback for the autocomplete address.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   A renderable array as expected by the render service.
   */
  public static function autoCompleteAddress(array &$form, FormStateInterface $form_state) {
    $triggeringElement = $form_state->getTriggeringElement();
    // We need the parent element, but since this is a Webform element the name
    // is not static.
    $parent = $triggeringElement['#parents'][0];
    $addressValues = $form_state->getValues();

    $zipcode = $addressValues[$parent]['postal_code'] ?? '';
    $houseNumber = $addressValues[$parent]['house_number'] ?? '';

    $address = NULL;
    if ($zipcode && $houseNumber) {
      $address = self::getAddress($zipcode, $houseNumber);
    }

    return [
      'wrapper' => [
        '#type' => 'container',
        '#attributes' => ['id' => $parent . '--wrapper', 'class' => ['form-wrapper']],
        'street' => [
          '#type' => 'textfield',
          '#title' => t('Street'),
          '#required' => TRUE,
          '#attributes' => ['data-webform-composite-id' => $parent . '--street'],
          '#value' => (is_object($address) ? $address->street : NULL),
        ],
        'city' => [
          '#type' => 'textfield',
          '#title' => t('City/Town'),
          '#required' => TRUE,
          '#attributes' => ['data-webform-composite-id' => $parent . '--city'],
          '#value' => (is_object($address) ? $address->city : NULL),
        ],
      ],
    ];
  }

  /**
   * Retrieves an address based on zipcode and housenumber.
   *
   * @param string $zipcode
   *   The zipcode.
   * @param string $houseNumber
   *   The house number.
   *
   * @return object|null
   *   An address object when available, or NULL.
   */
  private static function getAddress($zipcode, $houseNumber) {

    $api_url = \Drupal::config('webform_postcodeapi.settings')
      ->get('postcodenlapi_url');
    $api_key = \Drupal::config('webform_postcodeapi.settings')
      ->get('postcodenlapi_key');

    if (empty($zipcode) || empty($houseNumber) || empty($api_key)) {
      return NULL;
    }

    if (!FormValidation::isValidPostalcode($zipcode) || !FormValidation::isValidHouseNumber($houseNumber)) {
      return NULL;
    }

    $zipcode = preg_replace('/\s/', '', $zipcode);

    $client = new Client();

    try {
      $response = $client->get($api_url . '/' . $zipcode . '/' . $houseNumber, ['headers' => ['x-api-key' => $api_key]]);
    }
    catch (RequestException $e) {
      $message = ($e->hasResponse() ? 'message: ' . $e->getMessage() : 'message: none');
      \Drupal::logger('webform_postcodeapi')->error('Postcode NL API error, message: @message', ['@message' => $message]);
      return NULL;
    }

    return json_decode((string) $response->getBody());
  }

}
