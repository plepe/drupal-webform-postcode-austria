<?php

namespace Drupal\webform_postcodeapi\Element;

use Drupal\Component\Utility\Html;
use Drupal\webform\Element\WebformCompositeBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform_postcodeapi\Classes\FormValidation;

/**
 * Provides a 'webform_postcodeapi' composite webform element.
 *
 * Webform composites contain a group of sub-elements.
 *
 *
 * IMPORTANT:
 * Webform composite can not contain multiple value elements (i.e. checkboxes)
 * or composites (i.e. webform_address)
 *
 * @FormElement("webform_postcodeapi")
 *
 * @see \Drupal\webform\Element\WebformCompositeBase
 * @see \Drupal\webform_postcodeapi\Element\AlserdaWebformAutoaddress
 */
class WebformPostcodeAPI extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return parent::getInfo() + [
      '#theme' => 'webform_postcodeapi',
      '#element_validate' => [
          [static::class, 'validateWebformPostcodeAPI'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements(array $element) {
    // Generate an unique ID that can be used by #states.
    $html_id = Html::getUniqueId('webform_postcodeapi');

    $elements = [];
    $elements['postal_code'] = [
      '#type' => 'textfield',
      '#title' => t('Postal code'),
      '#required' => true,
      '#attributes' => ['data-webform-composite-id' => $html_id . '--postal_code'],
      /*'#ajax' => [
        'callback' => 'Drupal\webform_postcodeapi\FormAjax\AddressFormAjax::autoCompleteAddress',
        'wrapper' => $html_id . '--wrapper',
        'method' => 'replace',
        'event' => 'change',
      ],*/
    ];
    $elements['house_number'] = [
      '#type' => 'textfield',
      '#title' => t('House number'),
      '#required' => true,
      '#attributes' => ['data-webform-composite-id' => $html_id . '--house_number'],
      '#ajax' => [
        'callback' => 'Drupal\webform_postcodeapi\FormAjax\AddressFormAjax::autoCompleteAddress',
        'wrapper' => $html_id . '--wrapper',
        'method' => 'replace',
        'event' => 'change',
      ],
    ];
    $elements['house_number_addition'] = [
      '#type' => 'textfield',
      '#title' => t('House number addition'),
      '#required' => false,
      '#attributes' => ['data-webform-composite-id' => $html_id . '--house_number_addition'],
    ];
    $elements['wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => $html_id . '--wrapper']
    ];
    $elements['wrapper']['street'] = [
      '#type' => 'textfield',
      '#title' => t('Street'),
      '#required' => true,
      '#attributes' => ['data-webform-composite-id' => $html_id . '--street'],
    ];
    $elements['wrapper']['city'] = [
      '#type' => 'textfield',
      '#title' => t('City/Town'),
      '#required' => true,
      '#attributes' => ['data-webform-composite-id' => $html_id . '--city'],
    ];
    return $elements;
  }

  /**
   * Validate the form element.
   *
   * @param array $element
   * @param \Drupal\webform_postcodeapi\Element\FormStateInterface $form_state
   * @param array $complete_form
   */
  public static function validateWebformPostcodeAPI(&$element, FormStateInterface $form_state, &$complete_form) {
    $postal_code = $element['postal_code']['#value'];
    $house_number = $element['house_number']['#value'];
    $house_number_addition = $element['house_number_addition']['#value'];
    if (!FormValidation::isValidPostalCode($postal_code)) {
      $form_state->setError($element['postal_code'], t('The postal code is invalid.'));
    }

    if (!FormValidation::isValidHouseNumber($house_number)) {
      $form_state->setError($element['house_number'], t('The house number is invalid. Please use house number addition for additions to your house number.'));
    }

    if (!FormValidation::isValidHouseNumberAddition($house_number_addition)) {
      $form_state->setError($element['house_number_addition'], t('The house number addition is invalid, please use only numbers and/or letters.'));
    }
  }
}
