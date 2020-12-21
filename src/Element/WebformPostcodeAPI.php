<?php

namespace Drupal\webform_postcodeapi\Element;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
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
    $wrapper_id = Html::cleanCssIdentifier($html_id) . '--wrapper';

    $elements = [];
    $elements['zip_code'] = [
      '#type' => 'textfield',
      '#title' => t('Zip code'),
      '#required' => TRUE,
      '#prefix' => "<div id='$wrapper_id'>",
    ];
    $elements['house_number'] = [
      '#type' => 'number',
      '#title' => t('House number'),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => [static::class, 'autoCompleteAddress'],
        'wrapper' => $wrapper_id,
        'method' => 'replace',
        'event' => 'change',
        'progress' => ['type' => 'fullscreen'],
      ],
    ];
    $elements['house_number_ext'] = [
      '#type' => 'textfield',
      '#title' => t('House number addition'),
      '#required' => FALSE,
    ];
    $elements['wrapper'] = [
      '#type' => 'container',
    ];
    $elements['wrapper']['street'] = [
      '#type' => 'textfield',
      '#title' => t('Street'),
      '#required' => TRUE,
      '#after_build' => [[static::class, 'afterBuild']],
    ];
    $elements['wrapper']['town'] = [
      '#type' => 'textfield',
      '#title' => t('City/Town'),
      '#required' => TRUE,
      '#after_build' => [[static::class, 'afterBuild']],
      '#suffix' => '</div>',
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
      [':input[name="' . $composite_name . '[zip_code]"]' => ['empty' => TRUE]],
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

    $zipcode = $addressValues[$parent]['zip_code'] ?? '';
    $houseNumber = $addressValues[$parent]['house_number'] ?? '';

    // Remove the trigger element field.
    array_pop($triggeringElement['#array_parents']);
    $form_elements = NestedArray::getValue($form, $triggeringElement['#array_parents']);

    if ($zipcode && $houseNumber && FormValidation::isValidPostalCode($zipcode) && FormValidation::isValidHouseNumber($houseNumber)) {
      $address = \Drupal::service('webform_postcodeapi.address_lookup')->getAddress($zipcode, $houseNumber);
      $form_elements['wrapper']['street']['#value'] = $address['street'];
      $form_elements['wrapper']['town']['#value'] = $address['city'];
    }

    if (!FormValidation::isValidPostalCode($zipcode)) {
      $form_elements['zip_code']['#description'] = t('The postal code is invalid.');
      $form_elements['zip_code']['#attributes']['class'][] = 'error';
    }
    if (!FormValidation::isValidHouseNumber($houseNumber)) {
      $form_elements['house_number']['#description'] = t('The house number is invalid. Please use house number addition for additions to your house number.');
      $form_elements['house_number']['#attributes']['class'][] = 'error';
    }


    return $form_elements;
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
    $zip_code = $element['zip_code']['#value'];
    $house_number = $element['house_number']['#value'];
    $house_number_ext = $element['house_number_ext']['#value'];
    if (!FormValidation::isValidPostalCode($zip_code)) {
      $form_state->setError($element['zip_code'], t('The postal code is invalid.'));
    }

    if (!FormValidation::isValidHouseNumber($house_number)) {
      $form_state->setError($element['house_number'], t('The house number is invalid. Please use house number addition for additions to your house number.'));
    }

    if (!FormValidation::isValidHouseNumberAddition($house_number_ext)) {
      $form_state->setError($element['house_number_ext'], t('The house number addition is invalid, please use only numbers and/or letters.'));
    }
  }

}
