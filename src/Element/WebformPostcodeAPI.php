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
 */
class WebformPostcodeAPI extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return parent::getInfo() + ['#theme' => 'webform_postcodeapi'];
  }

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements(array $element) {
    // Generate an unique ID that can be used by #states.
    $html_id = $element['#webform_key'] ?? NULL;

    $elements = [];
    $elements['postal_code'] = [
      '#type' => 'textfield',
      '#title' => t('Postal code'),
      '#required' => TRUE,
    ];
    $elements['house_number'] = [
      '#type' => 'number',
      '#title' => t('House number'),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => [static::class, 'autoCompleteAddress'],
        'wrapper' => Html::cleanCssIdentifier($html_id) . '--wrapper',
        'method' => 'replace',
        'event' => 'change',
        'progress' => ['type' => 'fullscreen'],
      ],
    ];
    $elements['house_number_addition'] = [
      '#type' => 'textfield',
      '#title' => t('House number addition'),
      '#required' => FALSE,
    ];
    $elements['wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => Html::cleanCssIdentifier($html_id) . '--wrapper'],
    ];
    $elements['wrapper']['street'] = [
      '#type' => 'textfield',
      '#title' => t('Street'),
      '#required' => TRUE,
      '#after_build' => [[static::class, 'afterBuild']],
    ];
    $elements['wrapper']['city'] = [
      '#type' => 'textfield',
      '#title' => t('City/Town'),
      '#required' => TRUE,
      '#after_build' => [[static::class, 'afterBuild']],
    ];

    return $elements;
  }

  /**
   * Performs the after_build callback.
   */
  public static function afterBuild(array $element, FormStateInterface $form_state) {
    // Add #states targeting the specific element and table row.
    preg_match('/^(.+)\[[^]]+]$/', $element['#name'], $match);
    $composite_name = $match[1];
    $element['#states']['disabled'] = [
      [':input[name="' . $composite_name . '[postal_code]"]' => ['empty' => TRUE]],
      [':input[name="' . $composite_name . '[house_number]"]' => ['empty' => TRUE]],
    ];
    // Add .js-form-wrapper to wrapper (ie td) to prevent #states API from
    // disabling the entire table row when this element is disabled.
    $element['#wrapper_attributes']['class'][] = 'js-form-wrapper';
    return $element;
  }

  /**
   * Ajax callback function for the webform_postcodeapi element.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   A renderable array as expected by the render service.
   */
  public static function autoCompleteAddress(array $form, FormStateInterface $form_state) {
    $triggeringElement = $form_state->getTriggeringElement();
    // We need the parent element, but since this is a Webform element the name
    // is not static.
    $parent = $triggeringElement['#parents'][0];
    $addressValues = $form_state->getValues();

    $zipcode = $addressValues[$parent]['postal_code'] ?? '';
    $houseNumber = $addressValues[$parent]['house_number'] ?? '';

    if ($zipcode && $houseNumber) {
      $address = \Drupal::service('webform_postcodeapi.address_lookup')->getAddress($zipcode, $houseNumber);
      $form['elements'][$parent]['wrapper']['street']['#value'] = $address['street'];
      $form['elements'][$parent]['wrapper']['city']['#value'] = $address['city'];
    }

    return $form['elements'][$parent]['wrapper'];
  }

  /**
   * Validate the form element.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form for the form this element belongs to.
   */
  // phpcs:disable
  public static function validateWebformPostcodeAPI(array &$element, FormStateInterface $form_state) {
    // phpcs:enable
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
