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
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public static function autoCompleteAddress(array &$form, FormStateInterface $form_state) {
    $triggeringElement = $form_state->getTriggeringElement();
    // We need the parent element, but since this is a webform element the name is not static.
    $parent = $triggeringElement['#parents'][0];
    $addressValues = $form_state->getValues() ?? null;

    $zipcode = $addressValues[$parent]['postal_code'] ?? '';
    $houseNumber = $addressValues[$parent]['house_number'] ?? '';

    $address = null;
    if ($zipcode && $houseNumber) {
      $address = self::getAddress($zipcode, $houseNumber);
    }  
    
    return [
      'wrapper' => [
        '#type' => 'container',
        '#attributes' => ['id' => $form['elements'][$parent]['wrapper']['#attributes']['id']],
        'street' => [
          '#type' => 'textfield',
          '#title' => t('Street'),
          '#required' => TRUE,
          '#attributes' => ['data-webform-composite-id' => $form['elements'][$parent]['wrapper']['street']['#attributes']['data-webform-composite-id']],
          '#value' => (is_object($address) ? $address->street: null),
        ],
        'city' => [
          '#type' => 'textfield',
          '#title' => t('City/Town'),
          '#required' => TRUE,
          '#attributes' => ['data-webform-composite-id' => $form['elements'][$parent]['wrapper']['city']['#attributes']['data-webform-composite-id']],
          '#value' => (is_object($address) ? $address->city: null),
        ],
      ]
    ];
  }
  
  /**
   * Retrieves an address based on zipcode and housenumber
   *
   * @param string $zipcode
   * @param string $houseNumber
   * 
   * @return mixed|null
   */
  private static function getAddress($zipcode, $houseNumber) {

    $api_url = \Drupal::config('webform_postcodeapi.settings')
        ->get('postcodenlapi_url');    
    $api_key = \Drupal::config('webform_postcodeapi.settings')
        ->get('postcodenlapi_key');
    
    if (empty($zipcode) || empty($houseNumber) || empty($api_key)) {
      return null;
    }
    
    if (!FormValidation::isValidPostalcode($zipcode) || !FormValidation::isValidHouseNumber($houseNumber)) {
      return null;
    }

    $zipcode = preg_replace('/\s/', '', $zipcode);
    
    $client = new Client();

    try {
      $response = $client->get($api_url . '/' . $zipcode . '/' . $houseNumber, [ 'headers' => [ 'x-api-key' => $api_key ] ]);
    } catch (RequestException $e)  {
      $message = ($e->hasResponse() ? 'message: ' . $e->getMessage(): 'message: none'); 
      \Drupal::logger('webform_postcodeapi')->error('Postcode NL API error, message: @message', [ '@message' => $message ]);
      return null;
    }    
    
    return json_decode((string) $response->getBody());
  }
}
